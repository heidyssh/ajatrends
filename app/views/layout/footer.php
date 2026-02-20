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
<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('[data-toggle-pw]');
  if(!btn) return;

  const sel = btn.getAttribute('data-toggle-pw');
  const inp = document.querySelector(sel);
  if(!inp) return;

  const isPw = inp.type === 'password';
  inp.type = isPw ? 'text' : 'password';

  const icon = btn.querySelector('i');
  if(icon){
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
  }
});
document.addEventListener('change', function(e){
  const r = e.target;
  if(!r.matches('.avatar-item input[type="radio"][name="id_avatar"]')) return;

  document.querySelectorAll('.avatar-item').forEach(el => el.classList.remove('active'));
  const label = r.closest('.avatar-item');
  if(label) label.classList.add('active');
});
</script>
</body>
</html>
