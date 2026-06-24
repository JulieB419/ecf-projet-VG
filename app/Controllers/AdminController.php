<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\DB;
use App\Models\User;
use App\Models\Dish;
use App\Models\Menu;
use App\Models\Theme;
use App\Models\Diet;
use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\OpeningHours;
use App\Services\Mailer;
use App\Services\StatsStore;

final class AdminController extends Controller
{
    public function index(): void { Auth::requireRole(['admin']); $this->view('admin/index'); }

    // ====== Legal pages mini-CMS (Mentions légales & CGV) ======
    public function pagesForm(): void
    {
        Auth::requireRole(['admin']);

        $type = (string)($_GET['type'] ?? 'mentions_legales');
        if (!in_array($type, ['mentions_legales', 'cgv'], true)) {
            $type = 'mentions_legales';
        }

        $page = SitePage::get($type);
        if (!$page) {
            if ($type === 'mentions_legales') {
                $default = @file_get_contents(__DIR__ . '/../Views/legal/legal_default.html') ?: '';
                SitePage::upsert('mentions_legales', 'Mentions légales', $default);
            } else {
                $default = @file_get_contents(__DIR__ . '/../Views/legal/cgv_default.html') ?: '';
                SitePage::upsert('cgv', 'Conditions générales de vente', $default);
            }
            $page = SitePage::get($type);
        }

        $this->view('admin/pages/edit', [
            'type' => $type,
            'page' => $page,
            'saved' => isset($_GET['saved']),
        ]);
    }

    public function pagesSave(): void
    {
        Auth::requireRole(['admin']);

        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            $this->redirect('/administration/pages');
        }

        $type = (string)($_POST['type'] ?? 'mentions_legales');
        if (!in_array($type, ['mentions_legales', 'cgv'], true)) {
            $type = 'mentions_legales';
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $content = (string)($_POST['content'] ?? '');
        if ($title === '') {
            $title = $type === 'cgv' ? 'Conditions générales de vente' : 'Mentions légales';
        }

        SitePage::upsert($type, $title, $content);
        $this->redirect('/administration/pages?type=' . urlencode($type) . '&saved=1');
    }

    

    // ====== Traiteur: informations globales (footer) ======
    public function settingsForm(): void
    {
        Auth::requireRole(['admin']);

        $settings = SiteSetting::all();
        $hours = OpeningHours::all();
        $this->view('admin/settings/edit', [
            'settings' => $settings,
            'hours' => $hours,
            'saved' => isset($_GET['saved']),
        ]);
    }

    public function settingsSave(): void
    {
        Auth::requireRole(['admin']);

        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            $this->redirect('/administration/informations');
        }

        $addr = trim((string)($_POST['caterer_address'] ?? ''));
        $phone = trim((string)($_POST['caterer_phone'] ?? ''));
        $email = trim((string)($_POST['caterer_email'] ?? ''));

        if ($addr === '') $addr = "12 Rue des Gourmets\n33000 Bordeaux";
        SiteSetting::set('caterer_address', $addr);
        SiteSetting::set('caterer_phone', $phone);
        SiteSetting::set('caterer_email', $email);

        $this->redirect('/administration/informations?saved=1');
    }

    // ====== Traiteur: horaires d'ouverture (footer) ======
    public function openingHoursSave(): void
    {
        Auth::requireRole(['admin']);

        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            $this->redirect('/administration/informations');
        }

        $open = $_POST['open_time'] ?? [];
        $close = $_POST['close_time'] ?? [];
        $closed = $_POST['is_closed'] ?? [];

        $rows = [];
        for ($d = 0; $d <= 6; $d++) {
            $o = trim((string)($open[$d] ?? ''));
            $c = trim((string)($close[$d] ?? ''));
            $isClosed = isset($closed[$d]) && (string)$closed[$d] === '1';

            // Basic normalization.
            if ($isClosed) {
                $o = $o !== '' ? $o : '00:00:00';
                $c = $c !== '' ? $c : '00:00:00';
            } else {
                // Accept HH:MM or HH:MM:SS.
                if ($o === '') $o = '09:00:00';
                if ($c === '') $c = '18:00:00';
                if (preg_match('/^[0-9]{2}:[0-9]{2}$/', $o)) $o .= ':00';
                if (preg_match('/^[0-9]{2}:[0-9]{2}$/', $c)) $c .= ':00';
            }

            $rows[] = [
                'day_of_week' => $d,
                'open_time' => $o,
                'close_time' => $c,
                'is_closed' => $isClosed,
            ];
        }

        OpeningHours::updateMany($rows);
        $this->redirect('/administration/informations?saved=1');
    }

public function employees(): void
    {
        Auth::requireRole(['admin']);
        $q = trim((string)($_GET['q'] ?? ''));
        $sql = "SELECT id,email,first_name,last_name,is_active FROM users WHERE role='employee'";
        $p = [];
        if ($q!=='') { $sql .= " AND (email LIKE :q OR first_name LIKE :q OR last_name LIKE :q)"; $p['q']='%'.$q.'%'; }
        $sql .= " ORDER BY created_at DESC";
        $st = DB::pdo()->prepare($sql);
        $st->execute($p);
        $this->view('admin/employees', ['employees'=>$st->fetchAll(),'q'=>$q]);
    }

    public function employeeCreateForm(): void { Auth::requireRole(['admin']); $this->view('admin/employee_new'); }

    public function employeeCreate(): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/administration/employes');

        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $pw = (string)($_POST['password'] ?? '');
        if ($email==='' || $pw==='') { Session::flash('error','Champs requis.'); $this->redirect('/administration/employes/nouveau'); }
        if (User::findByEmail($email)) { Session::flash('error','Email déjà utilisé.'); $this->redirect('/administration/employes/nouveau'); }

        User::create([
          'role'=>'employee',
          'email'=>$email,
          'password_hash'=>password_hash($pw,PASSWORD_DEFAULT),
          'first_name'=>'Employé',
          'last_name'=>'Vite&Gourmand',
          'phone'=>'',
          'address'=>'',
        ]);

        Mailer::send($email,'Compte employé créé',"Contactez l'administrateur pour récupérer votre mot de passe (non envoyé par email).");
        Session::flash('success','Employé créé.');
        $this->redirect('/administration/employes');
    }

    public function employeeDisable(array $params): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/administration/employes');
        User::setActive((int)$params['id'], false);
        Session::flash('success','Compte désactivé.');
        $this->redirect('/administration/employes');
    }

    public function stats(): void
    {
        Auth::requireRole(['admin']);
        $menuId = !empty($_GET['menu_id']) ? (int)$_GET['menu_id'] : null;
        $from = !empty($_GET['from']) ? (string)$_GET['from'] : null;
        $to = !empty($_GET['to']) ? (string)$_GET['to'] : null;

        $menus = DB::pdo()->query("SELECT id,title FROM menus ORDER BY title")->fetchAll();
        $includeCancelled = !empty($_GET['include_cancelled']);

        $agg = StatsStore::aggregate($menuId, $from, $to, $includeCancelled);

        $this->view('admin/stats', compact('menus','agg','menuId','from','to','includeCancelled'));
    }


    // =========================
    // Gestion des plats
    // =========================
    public function dishes(): void
    {
        Auth::requireRole(['admin']);
        $this->view('admin/dishes/index', [
            'dishes' => Dish::all(),
        ]);
    }

    public function dishCreateForm(): void
    {
        Auth::requireRole(['admin']);
        $this->view('admin/dishes/create');
    }

    public function dishCreate(): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Session expirée.'); $this->redirect('/administration/plats'); }

        $name = trim((string)($_POST['name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $type = (string)($_POST['type'] ?? 'plat');
        $regimes = isset($_POST['regimes']) && is_array($_POST['regimes']) ? implode(',', array_map('strval', $_POST['regimes'])) : null;
        $allergens = isset($_POST['allergens']) && is_array($_POST['allergens']) ? implode(',', array_map('strval', $_POST['allergens'])) : null;

        if ($name === '' || mb_strlen($name) < 2) {
            Session::flash('error','Nom du plat invalide.');
            $this->redirect('/administration/plats/nouveau');
        }

        Dish::create(['name'=>$name,'description'=>$desc,'type'=>$type,'regimes'=>$regimes,'allergens'=>$allergens]);
        Session::flash('success','Plat ajouté.');
        $this->redirect('/administration/plats');
    }

    public function dishEditForm(string $id): void
    {
        Auth::requireRole(['admin']);
        $dish = Dish::find((int)$id);
        if (!$dish) { http_response_code(404); echo 'Plat introuvable'; return; }
        $this->view('admin/dishes/edit', ['dish'=>$dish]);
    }

    public function dishUpdate(string $id): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Session expirée.'); $this->redirect('/administration/plats'); }

        $dishId = (int)$id;
        $dish = Dish::find($dishId);
        if (!$dish) { http_response_code(404); echo 'Plat introuvable'; return; }

        $name = trim((string)($_POST['name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $type = (string)($_POST['type'] ?? ($dish['type'] ?? 'plat'));
        $regimes = isset($_POST['regimes']) && is_array($_POST['regimes']) ? implode(',', array_map('strval', $_POST['regimes'])) : null;
        $allergens = isset($_POST['allergens']) && is_array($_POST['allergens']) ? implode(',', array_map('strval', $_POST['allergens'])) : null;

        if ($name === '' || mb_strlen($name) < 2) {
            Session::flash('error','Nom du plat invalide.');
            $this->redirect('/administration/plats/'.$dishId.'/modifier');
        }

        Dish::update($dishId, ['name'=>$name,'description'=>$desc,'type'=>$type,'regimes'=>$regimes,'allergens'=>$allergens]);
        Session::flash('success','Plat modifié.');
        $this->redirect('/administration/plats');
    }

    public function dishDelete(string $id): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Session expirée.'); $this->redirect('/administration/plats'); }

        Dish::delete((int)$id);
        Session::flash('success','Plat supprimé.');
        $this->redirect('/administration/plats');
    }

    // =========================
    // Gestion des menus
    // =========================
    public function menus(): void
    {
        Auth::requireRole(['admin']);
        $this->view('admin/menus/index', [
            'menus' => Menu::allAdmin(),
        ]);
    }

    public function menuCreateForm(): void
    {
        Auth::requireRole(['admin']);
        $this->view('admin/menus/create', [
            'themes' => Theme::all(),
            'diets'  => Diet::all(),
            'dishes' => Dish::all(),
        ]);
    }

    public function menuCreate(): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Session expirée.'); $this->redirect('/administration/menus'); }

        $data = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'conditions' => trim((string)($_POST['conditions'] ?? '')),
            'theme_id' => (int)($_POST['theme_id'] ?? 0),
            'diet_id' => (int)($_POST['diet_id'] ?? 0),
            'min_people' => (int)($_POST['min_people'] ?? 1),
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'stock_available' => (int)($_POST['stock_available'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['title'] === '' || mb_strlen($data['title']) < 3) {
            Session::flash('error','Titre invalide.');
            $this->redirect('/administration/menus/nouveau');
        }

        $menuId = Menu::create($data);

        // images (1 par ligne)
        // Images: keep checked existing + add uploads
        $keep = array_values(array_filter(array_map('trim', (array)($_POST['images_existing'] ?? []))));
        $uploaded = Upload::saveImages($_FILES['images_upload'] ?? [], 'menus');
        $images = array_values(array_unique(array_merge($keep, $uploaded)));

        // Backward compatibility if no checkboxes + no upload: fallback to textarea
        if (empty($images)) {
            $imagesRaw = (string)($_POST['images'] ?? '');
            $images = array_values(array_filter(array_map('trim', preg_split('/
|
|
/', $imagesRaw) ?: [])));
        }

        Menu::setImages($menuId, $images);

        // plats liés
        $entrees = array_map('intval', (array)($_POST['entrees'] ?? []));
        $plats   = array_map('intval', (array)($_POST['plats'] ?? []));
        $desserts= array_map('intval', (array)($_POST['desserts'] ?? []));
        Menu::setDishes($menuId, $entrees, $plats, $desserts);

        Session::flash('success','Menu créé.');
        $this->redirect('/administration/menus');
    }

    public function menuEditForm(string $id): void
    {
        Auth::requireRole(['admin']);
        $menu = Menu::find((int)$id);
        if (!$menu) { http_response_code(404); echo 'Menu introuvable'; return; }

        // Dishes existants par catégorie
        $selected = ['entree'=>[], 'plat'=>[], 'dessert'=>[]];
        foreach (($menu['dishes'] ?? []) as $d) {
            $cat = (string)($d['category'] ?? '');
            if (!isset($selected[$cat])) continue;
            $selected[$cat][] = (int)($d['id'] ?? 0);
        }

        $this->view('admin/menus/edit', [
            'menu' => $menu,
            'themes' => Theme::all(),
            'diets'  => Diet::all(),
            'dishes' => Dish::all(),
            'selected' => $selected,
        ]);
    }

    public function menuUpdate(string $id): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Session expirée.'); $this->redirect('/administration/menus'); }

        $menuId = (int)$id;
        $menu = Menu::find($menuId);
        if (!$menu) { http_response_code(404); echo 'Menu introuvable'; return; }

        $data = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'conditions' => trim((string)($_POST['conditions'] ?? '')),
            'theme_id' => (int)($_POST['theme_id'] ?? 0),
            'diet_id' => (int)($_POST['diet_id'] ?? 0),
            'min_people' => (int)($_POST['min_people'] ?? 1),
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'stock_available' => (int)($_POST['stock_available'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        Menu::update($menuId, $data);

        // Images: uploads (and optional legacy textarea)
        $images = Upload::saveImages($_FILES['images_upload'] ?? [], 'menus');
        if (empty($images)) {
            $imagesRaw = (string)($_POST['images'] ?? '');
            $images = array_values(array_filter(array_map('trim', preg_split('/
|
|
/', $imagesRaw) ?: [])));
        }
        Menu::setImages($menuId, $images);

        $entrees = array_map('intval', (array)($_POST['entrees'] ?? []));
        $plats   = array_map('intval', (array)($_POST['plats'] ?? []));
        $desserts= array_map('intval', (array)($_POST['desserts'] ?? []));
        Menu::setDishes($menuId, $entrees, $plats, $desserts);

        Session::flash('success','Menu mis à jour.');
        $this->redirect('/administration/menus');
    }

    public function menuToggle(string $id): void
    {
        Auth::requireRole(['admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Session expirée.'); $this->redirect('/administration/menus'); }

        $menuId = (int)$id;
        $menu = Menu::find($menuId);
        if (!$menu) { http_response_code(404); echo 'Menu introuvable'; return; }

        Menu::setActive($menuId, ((int)$menu['is_active']) !== 1);
        Session::flash('success','Statut du menu mis à jour.');
        $this->redirect('/administration/menus');
    }

}
