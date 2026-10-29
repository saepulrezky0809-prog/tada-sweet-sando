<script>
document.querySelectorAll('[data-toggle-password]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = button.parentElement.querySelector('input');
    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    button.querySelector('[data-eye-open]').hidden = !isVisible;
    button.querySelector('[data-eye-closed]').hidden = isVisible;
    button.setAttribute('aria-label', isVisible ? 'Lihat password' : 'Sembunyikan password');
    button.title = isVisible ? 'Lihat password' : 'Sembunyikan password';
    button.setAttribute('aria-pressed', String(!isVisible));
  });
});
</script>
</body>
</html>
