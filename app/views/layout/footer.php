<?php
$page = $_GET['page'] ?? 'login';
$isAuthPage = in_array($page, ['login','register'], true);
$isLogged = isset($_SESSION['user']);
?>
<?php if ($isAuthPage || !$isLogged): ?>
  </main>
<?php else: ?>
      </main>
    </div>
  </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
