/**
 * 2009-2026 Tecnoacquisti.com
 * @license One Paid Licence By WebSite Using This Module.
 */
(() => {
  const editor = document.getElementById('tec-fl-category-editor');
  const dialog = document.getElementById('tec-fl-picker');
  const table = editor.querySelector('table');
  const field = editor.querySelector('input[name="admission_categories"]');
  const search = dialog.querySelector('input[type="search"]');
  const rate = dialog.querySelector('input[type="number"]');
  const choices = Array.from(dialog.querySelectorAll('input[type="checkbox"]'));
  const names = new Map(choices.map(input => [input.value, input.dataset.path]));
  const confirm = dialog.querySelector('#tec-fl-confirm');
  const error = dialog.querySelector('#tec-fl-limit-error');
  const rules = new Map(field.value.split(',').filter(Boolean).map(pair => pair.split('=')));

  function render() {
    const body = table.tBodies[0];
    body.textContent = '';
    rules.forEach((limit, id) => {
      const row = editor.querySelector('template').content.firstElementChild.cloneNode(true);
      row.dataset.category = id;
      row.cells[0].textContent = names.get(id) || ('#' + id);
      row.cells[1].textContent = limit;
      row.querySelector('[data-action="edit"]').disabled = !names.has(id);
      body.appendChild(row);
    });
    table.hidden = rules.size === 0;
    field.value = Array.from(rules, pair => pair.join('=')).join(',');
  }

  const folders = Array.from(dialog.querySelectorAll('.tec-fl-toggle'));
  const expanded = new Set();
  let matches = choices;

  function showBranch(button, open) {
    document.getElementById(button.getAttribute('aria-controls')).hidden = !open;
    button.setAttribute('aria-expanded', String(open));
    button.firstElementChild.className = open ? 'icon icon-folder-open' : 'icon icon-folder-close';
  }

  function filter() {
    const query = search.value.trim().toLocaleLowerCase();
    matches = choices.filter(input => names.get(input.value).toLocaleLowerCase().includes(query));
    const visible = new Set(matches.map(input => input.closest('li')));
    if (query) {
      matches.forEach(input => {
        let node = input.closest('li').parentElement.closest('li');
        while (node && !visible.has(node)) {
          visible.add(node);
          node = node.parentElement.closest('li');
        }
      });
    }
    choices.forEach(input => {
      const node = input.closest('li');
      node.hidden = !visible.has(node);
    });
    folders.forEach(button => showBranch(button, query ? true : expanded.has(button)));
    dialog.querySelector('#tec-fl-no-matches').hidden = matches.length > 0;
  }

  function updateSelection() {
    const selected = choices.filter(input => input.checked);
    const extra = selected.filter(input => !rules.has(input.value)).length;
    error.hidden = rules.size + extra <= 64;
    confirm.disabled = selected.length === 0 || !error.hidden;
  }

  function openPicker(id) {
    dialog.querySelector('form').reset();
    choices.forEach(input => { input.checked = input.value === id; });
    expanded.clear();
    const current = choices.find(input => input.checked);
    if (current) {
      let node = current.closest('li').parentElement.closest('li');
      while (node) {
        expanded.add(node.firstElementChild);
        node = node.parentElement.closest('li');
      }
    } else if (folders.length) {
      expanded.add(folders[0]);
    }
    rate.value = rules.get(id) || '';
    filter();
    updateSelection();
    dialog.showModal();
    if (id) rate.focus();
    else search.focus();
  }

  editor.querySelector('#tec-fl-add').addEventListener('click', () => openPicker());
  table.addEventListener('click', event => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const id = button.closest('tr').dataset.category;
    if (button.dataset.action === 'edit') {
      openPicker(id);
    } else {
      rules.delete(id);
      render();
      editor.querySelector('#tec-fl-add').focus();
    }
  });
  dialog.addEventListener('click', event => {
    const toggle = event.target.closest('.tec-fl-toggle');
    if (toggle) {
      const open = toggle.getAttribute('aria-expanded') !== 'true';
      if (open) expanded.add(toggle);
      else expanded.delete(toggle);
      showBranch(toggle, open);
      return;
    }
    const action = event.target.closest('[data-tree-action]');
    if (!action) return;
    switch (action.dataset.treeAction) {
      case 'expand':
      case 'collapse': {
        const open = action.dataset.treeAction === 'expand';
        expanded.clear();
        folders.forEach(button => {
          if (open) expanded.add(button);
          showBranch(button, open);
        });
        break;
      }
      case 'select':
        matches.forEach(input => { input.checked = true; });
        updateSelection();
        break;
      case 'clear':
        choices.forEach(input => { input.checked = false; });
        updateSelection();
        break;
      default:
        break;
    }
  });
  search.addEventListener('input', filter);
  dialog.addEventListener('change', updateSelection);
  dialog.querySelector('#tec-fl-cancel').addEventListener('click', () => dialog.close());
  dialog.querySelector('form').addEventListener('submit', event => {
    event.preventDefault();
    if (confirm.disabled || !rate.checkValidity()) return;
    choices.filter(input => input.checked).forEach(input => rules.set(input.value, String(rate.valueAsNumber)));
    dialog.close();
    render();
    editor.querySelector('#tec-fl-add').focus();
  });
  render();
})();
