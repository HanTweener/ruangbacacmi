const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

function page(reducedMotion = false) {
  const scrolls = [];
  const history = [];
  let click;
  const location = { hash: '' };
  const link = { addEventListener: (type, handler) => { click = handler; } };
  const section = { scrollIntoView: options => scrolls.push(options) };

  vm.runInNewContext(readFileSync('public/assets/app.js', 'utf8'), {
    document: {
      querySelector: selector => selector === '.hero a[href="#artikel"]' ? link : null,
      querySelectorAll: () => [],
      getElementById: id => id === 'artikel' ? section : null,
    },
    window: {
      location,
      history: { pushState: (state, title, url) => { history.push(url); location.hash = url; } },
      matchMedia: () => ({ matches: reducedMotion }),
    },
  });

  return {
    scrolls,
    history,
    click(overrides = {}) {
      let prevented = false;
      click({ button: 0, preventDefault() { prevented = true; }, ...overrides });
      return prevented;
    },
  };
}

test('the article button smooth-scrolls once without native anchor navigation', () => {
  const ui = page();
  assert.equal(ui.click(), true);
  assert.equal(ui.scrolls.length, 1);
  assert.equal(ui.scrolls[0].behavior, 'smooth');
  assert.equal(ui.scrolls[0].block, 'start');
  assert.deepEqual(ui.history, ['#artikel']);

  ui.click();
  assert.deepEqual(ui.history, ['#artikel']);
});

test('the article button respects reduced-motion preferences', () => {
  const ui = page(true);
  assert.equal(ui.click(), true);
  assert.equal(ui.scrolls[0].behavior, 'auto');
});

test('modified clicks and previously handled clicks keep their native behavior', () => {
  const ui = page();
  for (const overrides of [
    { ctrlKey: true }, { metaKey: true }, { shiftKey: true }, { altKey: true },
    { button: 1 }, { defaultPrevented: true },
  ]) {
    assert.equal(ui.click(overrides), false);
  }
  assert.equal(ui.scrolls.length, 0);
  assert.equal(ui.history.length, 0);
});
