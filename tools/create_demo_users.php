<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\DB;

$pdo = DB::pdo();

$users = [
  ['role'=>'admin', 'email'=>'admin@vite-gourmand.test', 'password'=>'Admin!12345', 'first'=>'Julie', 'last'=>'Admin'],
  ['role'=>'employee', 'email'=>'employe@vite-gourmand.test', 'password'=>'Employe!12345', 'first'=>'José', 'last'=>'Employé'],
  ['role'=>'user', 'email'=>'client@vite-gourmand.test', 'password'=>'Client!12345', 'first'=>'Camille', 'last'=>'Client'],
];

foreach ($users as $u) {
  $exists = $pdo->prepare("SELECT id FROM users WHERE email=:e");
  $exists->execute(['e'=>$u['email']]);
  if ($exists->fetch()) {
    echo "OK déjà présent : {$u['email']}\n";
    continue;
  }

  $stmt = $pdo->prepare("INSERT INTO users (role,email,password_hash,first_name,last_name,phone,address,is_active,created_at)
                         VALUES (:role,:email,:hash,:first,:last,:phone,:addr,1,NOW())");
  $stmt->execute([
    'role'=>$u['role'],
    'email'=>$u['email'],
    'hash'=>password_hash($u['password'], PASSWORD_DEFAULT),
    'first'=>$u['first'],
    'last'=>$u['last'],
    'phone'=>'0600000000',
    'addr'=>'Bordeaux',
  ]);

  echo "Créé : {$u['email']}\n";
}

echo "Terminé.\n";
