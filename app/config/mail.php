<?php
declare(strict_types=1);

return [
  'enabled' => true,

  // Gmail o Google Workspace
  'host' => 'smtp.gmail.com',
  'port' => 587,
  'secure' => 'tls',

  // correo que enviará
  'username' => 'tu_correo_empresarial@gmail.com',
  'password' => 'AQUI_VA_TU_APP_PASSWORD',

  'from_email' => 'tu_correo_empresarial@gmail.com',
  'from_name'  => 'AJA Trends Notificaciones',

  // correos que recibirán TODO
  'notify_to' => [
    'tu_gmail@gmail.com'
  ],
];