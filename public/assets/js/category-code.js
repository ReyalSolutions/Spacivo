// Category codes use the same 2–80 character format as server validation.
window.categoryCodeFromName = function (name) {
  if (!name.trim()) return '';
  var code = name.normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
    .replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
  if (!code) code = 'category';
  if (!/^[a-z]/.test(code)) code = 'category_' + code;
  if (code.length < 2) code += '_category';
  return code.slice(0, 80).replace(/_+$/, '');
};
document.querySelectorAll('.category-form').forEach(function (form) {
  // Saved codes are stable; only new categories follow name changes.
  if (Number(form.dataset.id) !== 0) return;
  var name = form.elements.name, code = form.elements.slug;
  function update() {
    var base = window.categoryCodeFromName(name.value), candidate = base, suffix = 2;
    var taken = new Set(Array.from(document.querySelectorAll('.category-form')).filter(function (other) {
      return other !== form && Number(other.dataset.id) > 0;
    }).map(function (other) { return other.elements.slug.value; }));
    while (candidate && taken.has(candidate)) {
      var ending = '_' + suffix++;
      candidate = base.slice(0, 80 - ending.length).replace(/_+$/, '') + ending;
    }
    code.value = candidate;
  }
  name.addEventListener('input', update);
  update();
});
