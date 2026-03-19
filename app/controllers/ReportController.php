<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Report.php';

final class ReportController
{
  public static function handle(array $get): array
  {
    require_auth();

    $filters = Report::filters($get);
    return Report::reportPack($filters);
  }
}