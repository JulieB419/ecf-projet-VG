<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

/**
 * StatsStore
 * - Source de vérité : MySQL (métier)
 * - MongoDB : data-mart statistiques (agrégations admin)
 * - Fallback local : fichier JSONL (dev/offline)
 */
final class StatsStore
{
    private const DEFAULT_DB = 'vite_gourmand';
    private const DEFAULT_COLLECTION = 'order_stats';
    private const FALLBACK_PATH = __DIR__ . '/../../storage/cache/order_stats.jsonl';

    /**
     * Enregistre (ou met à jour) une commande "confirmée" dans la base stats.
     * $doc doit contenir au minimum: order_id, menu_id, total_price, created_date (YYYY-MM-DD)
     */
    public static function recordOrder(array $doc): void
    {
        $doc = self::normalizeOrderDoc($doc);

        // MongoDB si disponible
        if (extension_loaded('mongodb')) {
            try {
                $uri = Env::get('MONGO_URI');
                $dbName = Env::get('MONGO_DB', self::DEFAULT_DB);
                $collection = Env::get('MONGO_STATS_COLLECTION', self::DEFAULT_COLLECTION);

                if ($uri) {
                    $manager = new \MongoDB\Driver\Manager($uri);

                    // Upsert idempotent par order_id (évite doublons)
                    $bulk = new \MongoDB\Driver\BulkWrite();
                    $bulk->update(
                        ['order_id' => (int)$doc['order_id']],
                        ['$set' => $doc, '$setOnInsert' => ['created_at' => new \MongoDB\BSON\UTCDateTime()]],
                        ['upsert' => true]
                    );

                    $manager->executeBulkWrite($dbName . '.' . $collection, $bulk);
                    return;
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Fallback JSONL (append-only)
        self::appendJsonl(['type' => 'order', 'payload' => $doc]);
    }

    /**
     * Marque une commande comme annulée (pour pouvoir l'inclure/exclure dans les stats).
     */
    public static function markCancelled(int $orderId): void
    {
        // MongoDB si dispo
        if (extension_loaded('mongodb')) {
            try {
                $uri = Env::get('MONGO_URI');
                $dbName = Env::get('MONGO_DB', self::DEFAULT_DB);
                $collection = Env::get('MONGO_STATS_COLLECTION', self::DEFAULT_COLLECTION);

                if ($uri) {
                    $manager = new \MongoDB\Driver\Manager($uri);
                    $bulk = new \MongoDB\Driver\BulkWrite();
                    $bulk->update(
                        ['order_id' => $orderId],
                        ['$set' => ['status' => 'cancelled', 'cancelled_at' => new \MongoDB\BSON\UTCDateTime()]],
                        ['upsert' => false]
                    );
                    $manager->executeBulkWrite($dbName . '.' . $collection, $bulk);
                    return;
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Fallback JSONL
        self::appendJsonl(['type' => 'cancel', 'payload' => ['order_id' => $orderId, 'status' => 'cancelled', 'date' => date('c')]]);
    }

    /**
     * Agrégations statistiques pour l'admin.
     * Retour:
     *  - source: mongo|file|none
     *  - total: CA total
     *  - count: nb commandes
     *  - avg: panier moyen
     *  - by_menu: liste [{menu_id, menu_title, total, count}]
     */
    public static function aggregate(?int $menuId, ?string $from, ?string $to, bool $includeCancelled = false): array
    {
        // MongoDB
        if (extension_loaded('mongodb')) {
            try {
                $uri = Env::get('MONGO_URI');
                $dbName = Env::get('MONGO_DB', self::DEFAULT_DB);
                $collection = Env::get('MONGO_STATS_COLLECTION', self::DEFAULT_COLLECTION);

                if ($uri) {
                    $manager = new \MongoDB\Driver\Manager($uri);

                    $match = [];
                    if ($menuId) $match['menu_id'] = $menuId;

                    // Filtrage date simple sur created_date (YYYY-MM-DD)
                    if ($from) $match['created_date'] = array_merge($match['created_date'] ?? [], ['$gte' => $from]);
                    if ($to)   $match['created_date'] = array_merge($match['created_date'] ?? [], ['$lte' => $to]);

                    if (!$includeCancelled) {
                        $match['status'] = ['$ne' => 'cancelled'];
                    }

                    // Global totals
                    $pipelineTotals = [
                        ['$match' => $match],
                        ['$group' => [
                            '_id' => null,
                            'total' => ['$sum' => '$total_price'],
                            'count' => ['$sum' => 1],
                        ]],
                    ];

                    $cmdTotals = new \MongoDB\Driver\Command([
                        'aggregate' => $collection,
                        'pipeline' => $pipelineTotals,
                        'cursor' => new \stdClass(),
                    ]);
                    $totCursor = $manager->executeCommand($dbName, $cmdTotals);
                    $totArr = $totCursor->toArray();
                    $total = isset($totArr[0]->total) ? (float)$totArr[0]->total : 0.0;
                    $count = isset($totArr[0]->count) ? (int)$totArr[0]->count : 0;
                    $avg = $count > 0 ? $total / $count : 0.0;

                    // By menu totals + count
                    $pipelineByMenu = [
                        ['$match' => $match],
                        ['$group' => [
                            '_id' => ['menu_id' => '$menu_id', 'menu_title' => '$menu_title'],
                            'total' => ['$sum' => '$total_price'],
                            'count' => ['$sum' => 1],
                        ]],
                        ['$sort' => ['total' => -1]],
                    ];

                    $cmdByMenu = new \MongoDB\Driver\Command([
                        'aggregate' => $collection,
                        'pipeline' => $pipelineByMenu,
                        'cursor' => new \stdClass(),
                    ]);
                    $byCursor = $manager->executeCommand($dbName, $cmdByMenu);
                    $byMenu = [];
                    foreach ($byCursor as $row) {
                        $byMenu[] = [
                            'menu_id' => (int)($row->_id->menu_id ?? 0),
                            'menu_title' => (string)($row->_id->menu_title ?? ''),
                            'total' => (float)($row->total ?? 0),
                            'count' => (int)($row->count ?? 0),
                        ];
                    }

                    return [
                        'source' => 'mongo',
                        'total' => $total,
                        'count' => $count,
                        'avg' => $avg,
                        'by_menu' => $byMenu,
                    ];
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Fallback fichier JSONL
        if (!is_file(self::FALLBACK_PATH)) {
            return ['source' => 'none', 'total' => 0.0, 'count' => 0, 'avg' => 0.0, 'by_menu' => []];
        }

        $fromTs = $from ? strtotime($from . ' 00:00:00') : null;
        $toTs = $to ? strtotime($to . ' 23:59:59') : null;

        // Reconstitue l'état "dernier status" par order_id
        $orders = []; // order_id => doc
        $cancelled = []; // order_id => true

        foreach (file(self::FALLBACK_PATH, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $row = json_decode($line, true);
            if (!is_array($row)) continue;
            $type = $row['type'] ?? 'order';
            $payload = $row['payload'] ?? null;
            if (!is_array($payload)) continue;

            if ($type === 'order' && isset($payload['order_id'])) {
                $oid = (int)$payload['order_id'];
                $orders[$oid] = self::normalizeOrderDoc($payload);
                if (($orders[$oid]['status'] ?? 'confirmed') === 'cancelled') {
                    $cancelled[$oid] = true;
                }
            } elseif ($type === 'cancel' && isset($payload['order_id'])) {
                $cancelled[(int)$payload['order_id']] = true;
            }
        }

        $total = 0.0; $count = 0;
        $by = []; // menu_id => ['menu_id'=>, 'menu_title'=>, 'total'=>, 'count'=>]

        foreach ($orders as $doc) {
            $oid = (int)$doc['order_id'];
            $isCancelled = isset($cancelled[$oid]) || (($doc['status'] ?? '') === 'cancelled');
            if (!$includeCancelled && $isCancelled) continue;

            $createdDate = (string)($doc['created_date'] ?? '');
            $ts = $createdDate ? strtotime($createdDate . ' 12:00:00') : null;
            if ($fromTs && $ts !== null && $ts < $fromTs) continue;
            if ($toTs && $ts !== null && $ts > $toTs) continue;
            if ($menuId && (int)$doc['menu_id'] !== $menuId) continue;

            $mid = (int)$doc['menu_id'];
            if (!isset($by[$mid])) {
                $by[$mid] = [
                    'menu_id' => $mid,
                    'menu_title' => (string)($doc['menu_title'] ?? ''),
                    'total' => 0.0,
                    'count' => 0,
                ];
            }
            $by[$mid]['total'] += (float)$doc['total_price'];
            $by[$mid]['count'] += 1;

            $total += (float)$doc['total_price'];
            $count += 1;
        }

        usort($by, fn($a,$b) => ($b['total'] <=> $a['total']));

        return [
            'source' => 'file',
            'total' => $total,
            'count' => $count,
            'avg' => $count > 0 ? $total / $count : 0.0,
            'by_menu' => array_values($by),
        ];
    }

    // -------------------------
    // Helpers
    // -------------------------
    private static function appendJsonl(array $row): void
    {
        $dir = dirname(self::FALLBACK_PATH);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        file_put_contents(self::FALLBACK_PATH, json_encode($row, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    }

    private static function normalizeOrderDoc(array $doc): array
    {
        // Compat avec ancienne structure (date/total_amount)
        if (!isset($doc['created_date']) && isset($doc['date'])) {
            $doc['created_date'] = (string)$doc['date'];
        }
        if (!isset($doc['total_price']) && isset($doc['total_amount'])) {
            $doc['total_price'] = (float)$doc['total_amount'];
        }
        if (!isset($doc['menu_title'])) $doc['menu_title'] = (string)($doc['menu_title'] ?? '');
        if (!isset($doc['status'])) $doc['status'] = (string)($doc['status'] ?? 'confirmed');

        $doc['order_id'] = (int)($doc['order_id'] ?? 0);
        $doc['menu_id'] = (int)($doc['menu_id'] ?? 0);
        $doc['people_count'] = (int)($doc['people_count'] ?? 0);
        $doc['delivery_fee'] = (float)($doc['delivery_fee'] ?? 0.0);
        $doc['total_price'] = (float)($doc['total_price'] ?? 0.0);

        // created_date attendu YYYY-MM-DD
        if (!empty($doc['created_date'])) {
            $doc['created_date'] = substr((string)$doc['created_date'], 0, 10);
        } else {
            $doc['created_date'] = date('Y-m-d');
        }

        return $doc;
    }
}
