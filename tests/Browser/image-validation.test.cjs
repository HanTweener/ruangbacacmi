const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

function form() {
  const handlers = {};
  const images = [];
  const input = {
    files: [], dataset: { maxBytes: '2097152', maxWidth: '5000', maxHeight: '5000' },
    addEventListener: (name, handler) => { handlers[name] = handler; },
    setCustomValidity(message) { this.validationMessage = message; },
    setAttribute() {}, removeAttribute() {},
  };
  const preview = { hidden: true, getAttribute: () => null, removeAttribute() {} };
  const error = { hidden: true, textContent: '' };
  const fields = { gambar: input, 'image-preview': preview, 'image-error': error };
  vm.runInNewContext(readFileSync('public/assets/app.js', 'utf8'), {
    document: { querySelector: () => null, querySelectorAll: () => [], getElementById: id => fields[id] },
    URL: { createObjectURL: () => 'blob:synthetic', revokeObjectURL() {} },
    Image: function () { images.push(this); },
  });
  return { input, preview, error, images, select(file) { input.files = file ? [file] : []; handlers.change(); } };
}

test('the provided picture size is rejected before allocating an image preview', () => {
  const ui = form();
  ui.select({ type: 'image/jpeg', size: 3722735 });
  assert.match(ui.error.textContent, /3,55 MB; maksimal 2 MB/);
  assert.equal(ui.error.hidden, false);
  assert.equal(ui.input.validationMessage, ui.error.textContent);
  assert.equal(ui.images.length, 0);
  assert.equal(ui.preview.hidden, true);
});

test('a small file with excessive dimensions remains invalid', () => {
  const ui = form();
  ui.select({ type: 'image/png', size: 1000 });
  Object.assign(ui.images[0], { naturalWidth: 8520, naturalHeight: 5680 });
  ui.images[0].onload();
  assert.match(ui.error.textContent, /8520 × 5680.*5000 × 5000/);
  assert.equal(ui.input.validationMessage, ui.error.textContent);
  assert.equal(ui.preview.hidden, true);
});

test('choosing a valid replacement clears the error and shows its preview', () => {
  const ui = form();
  ui.select({ type: 'image/jpeg', size: 3722735 });
  ui.select({ type: 'image/jpeg', size: 500000 });
  Object.assign(ui.images[0], { naturalWidth: 1920, naturalHeight: 1280 });
  ui.images[0].onload();
  assert.equal(ui.input.validationMessage, '');
  assert.equal(ui.error.hidden, true);
  assert.equal(ui.preview.hidden, false);
  assert.equal(ui.preview.src, 'blob:synthetic');
});

test('a stale decode callback cannot overwrite a newer invalid selection', () => {
  const ui = form();
  ui.select({ type: 'image/png', size: 1000 });
  const previous = ui.images[0];
  ui.select({ type: 'image/jpeg', size: 3722735 });
  Object.assign(previous, { naturalWidth: 100, naturalHeight: 100 });
  previous.onload();
  assert.match(ui.input.validationMessage, /maksimal 2 MB/);
  assert.equal(ui.preview.hidden, true);
  ui.select(null);
  assert.equal(ui.input.validationMessage, '');
  assert.equal(ui.error.hidden, true);
});
