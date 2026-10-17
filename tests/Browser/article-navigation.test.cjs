const { test } = require("node:test");
const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const vm = require("node:vm");

function page({ reducedMotion = false, scrollY = 500 } = {}) {
    const base = "http://localhost/";
    const requests = [];
    const history = [];
    const scrolls = [];
    const focused = [];
    const listeners = {};
    const handlers = {};
    const timers = new Map();
    const location = { href: base + "#artikel" };
    const status = { textContent: "" };
    const error = { hidden: true };
    const retry = { href: "" };
    const attributes = new Map();

    function fixture(heading, count = 3) {
        return {
            heading,
            count,
            querySelector(selector) {
                return selector === "h2" ? { textContent: heading } : null;
            },
        };
    }

    const content = {
        current: fixture("Artikel terbaru."),
        replaceChildren(fixture) {
            this.current = fixture;
        },
        querySelector(selector) {
            return {
                textContent: this.current.heading,
                focus: (options) => focused.push({ selector, options }),
            };
        },
        querySelectorAll() {
            return Array(this.current.count).fill({});
        },
    };

    const window = {
        location,
        scrollY,
        history: {
            scrollRestoration: "auto",
            pushState(state, title, url) {
                location.href = new URL(url, base).href;
                history.push(location.href);
            },
        },
        getComputedStyle: () => ({ scrollPaddingTop: "25px" }),
        matchMedia: () => ({ matches: reducedMotion }),
        scrollTo(options) {
            scrolls.push(options);
            this.scrollY = options.top;
        },
        addEventListener(type, handler) {
            (listeners[type] ??= new Set()).add(handler);
        },
        removeEventListener(type, handler) {
            listeners[type]?.delete(handler);
        },
        setTimeout(handler) {
            timers.set(handler, handler);
            return handler;
        },
        clearTimeout(handler) {
            timers.delete(handler);
        },
    };
    const section = {
        dataset: { articleNavigation: base },
        style: { minHeight: "" },
        getBoundingClientRect: () => ({
            top: 525 - window.scrollY,
            height: 900,
        }),
        setAttribute: (key, value) => attributes.set(key, value),
        removeAttribute: (key) => attributes.delete(key),
        addEventListener: (type, handler) => {
            handlers[type] = handler;
        },
        querySelector: (selector) =>
            ({
                "[data-article-content]": content,
                "[data-article-status]": status,
                "[data-article-error]": error,
                "[data-article-retry]": retry,
            })[selector],
    };

    vm.runInNewContext(readFileSync("public/assets/app.js", "utf8"), {
        document: {
            documentElement: {},
            querySelector: (selector) =>
                selector === "[data-article-navigation]" ? section : null,
            querySelectorAll: () => [],
            getElementById: () => null,
        },
        window,
        URL,
        URLSearchParams,
        AbortController,
        FormData: class {
            constructor(form) {
                this.fields = form.fields;
            }
            *[Symbol.iterator]() {
                yield* this.fields;
            }
        },
        DOMParser: class {
            parseFromString(html) {
                const data = JSON.parse(html);
                const replacement = data.missing
                    ? null
                    : {
                          childNodes: [fixture(data.heading, data.count)],
                          querySelector: () => (data.noHeading ? null : {}),
                      };
                return { querySelector: () => replacement };
            }
        },
        fetch(url, options) {
            return new Promise((resolve, reject) =>
                requests.push({ url, options, resolve, reject }),
            );
        },
    });

    return {
        requests,
        history,
        scrolls,
        focused,
        window,
        section,
        content,
        status,
        error,
        retry,
        attributes,
        emit(type) {
            for (const handler of [...(listeners[type] ?? [])]) handler();
        },
        click(url, kind = ".category-tabs a", overrides = {}) {
            let prevented = false;
            const link = {
                href: new URL(url, base).href,
                target: "",
                hasAttribute: () => false,
                matches: (selector) => selector.includes(kind),
            };
            handlers.click({
                target: { closest: () => link },
                button: 0,
                preventDefault() {
                    prevented = true;
                },
                ...overrides,
            });
            return prevented;
        },
        submit(fields) {
            let prevented = false;
            handlers.submit({
                target: {
                    matches: () => true,
                    action: base + "#artikel",
                    fields,
                },
                preventDefault() {
                    prevented = true;
                },
            });
            return prevented;
        },
        async resolve(index, data, response = {}) {
            requests[index].resolve({
                ok: true,
                redirected: false,
                text: async () => JSON.stringify(data),
                ...response,
            });
            await new Promise(setImmediate);
        },
        async reject(index) {
            requests[index].reject(new Error("Synthetic network failure"));
            await new Promise(setImmediate);
        },
    };
}

test("category results replace in place, preserve the query, and use delegated listeners", async () => {
    const ui = page();
    assert.equal(ui.click("?q=menu&kategori=Nutrisi"), true);
    assert.equal(ui.content.current.heading, "Artikel terbaru.");
    assert.equal(ui.attributes.get("aria-busy"), "true");
    assert.equal(ui.requests[0].options.credentials, "same-origin");
    await ui.resolve(0, { heading: "Artikel Nutrisi.", count: 2 });
    assert.equal(ui.content.current.heading, "Artikel Nutrisi.");
    assert.equal(
        new URL(ui.window.location.href).searchParams.get("q"),
        "menu",
    );
    assert.equal(ui.focused[0].selector, ".category-tabs .selected");
    assert.equal(ui.scrolls.length, 0);
    assert.equal(ui.section.style.minHeight, "");
    assert.equal(ui.attributes.has("aria-busy"), false);
    assert.match(ui.status.textContent, /2 artikel/);
    ui.click("?kategori=Kegiatan");
    await ui.resolve(1, { heading: "Artikel Kegiatan." });
    assert.equal(ui.content.current.heading, "Artikel Kegiatan.");
});

test("rapid clicks abort old requests and ignore late responses", async () => {
    const ui = page();
    ui.click("?kategori=Kesehatan");
    ui.click("?kategori=Nutrisi");
    assert.equal(ui.requests[0].options.signal.aborted, true);
    await ui.resolve(1, { heading: "Artikel Nutrisi." });
    await ui.resolve(0, { heading: "Artikel Kesehatan." });
    assert.equal(ui.content.current.heading, "Artikel Nutrisi.");
    assert.equal(ui.history.length, 1);
    assert.match(ui.history[0], /kategori=Nutrisi/);
});

test("failed requests keep the results and URL intact and allow retry", async () => {
    const ui = page();
    const originalUrl = ui.window.location.href;
    ui.click("?kategori=Nutrisi");
    await ui.reject(0);
    assert.equal(ui.content.current.heading, "Artikel terbaru.");
    assert.equal(ui.window.location.href, originalUrl);
    assert.equal(ui.error.hidden, false);
    assert.equal(ui.attributes.has("aria-busy"), false);
    ui.click(ui.retry.href, "[data-article-retry]");
    await ui.resolve(1, { heading: "Artikel Nutrisi." });
    assert.equal(ui.error.hidden, true);
    assert.equal(ui.content.current.heading, "Artikel Nutrisi.");
});

test("malformed responses are rejected before changing the results", async () => {
    const ui = page();
    ui.click("?kategori=Nutrisi");
    await ui.resolve(0, { noHeading: true });
    assert.equal(ui.content.current.heading, "Artikel terbaru.");
    assert.equal(ui.history.length, 0);
    assert.equal(ui.error.hidden, false);
});

test("HTTP errors and redirects keep the existing results", async () => {
    for (const response of [{ ok: false }, { redirected: true }]) {
        const ui = page();
        ui.click("?kategori=Nutrisi");
        await ui.resolve(0, { heading: "Unexpected results" }, response);
        assert.equal(ui.content.current.heading, "Artikel terbaru.");
        assert.equal(ui.history.length, 0);
        assert.equal(ui.error.hidden, false);
    }
});

test("search and pagination preserve their query parameters and focus", async () => {
    const ui = page();
    assert.equal(
        ui.submit([
            ["q", "label pangan"],
            ["kategori", "Nutrisi"],
        ]),
        true,
    );
    const searchUrl = new URL(ui.requests[0].url);
    assert.equal(searchUrl.searchParams.get("q"), "label pangan");
    assert.equal(searchUrl.searchParams.get("kategori"), "Nutrisi");
    assert.equal(searchUrl.searchParams.has("page"), false);
    await ui.resolve(0, { heading: "Artikel Nutrisi." });
    assert.equal(ui.focused.at(-1).selector, "#public-search");
    ui.click("?q=label+pangan&kategori=Nutrisi&page=2", ".pagination a");
    await ui.resolve(1, { heading: "Artikel Nutrisi." });
    assert.equal(
        new URL(ui.window.location.href).searchParams.get("page"),
        "2",
    );
    assert.equal(ui.focused.at(-1).selector, "h2");
});

test("Back and Forward restore results and remembered positions without adding history entries", async () => {
    const ui = page();
    const firstUrl = ui.window.location.href;
    ui.click("?kategori=Nutrisi");
    await ui.resolve(0, { heading: "Artikel Nutrisi." });
    const nextUrl = ui.window.location.href;
    ui.window.scrollY = 700;
    ui.emit("scroll");
    ui.window.location.href = firstUrl;
    ui.emit("popstate");
    await ui.resolve(1, { heading: "Artikel terbaru." });
    assert.equal(ui.scrolls.at(-1).top, 500);
    assert.equal(ui.scrolls.at(-1).behavior, "auto");
    ui.window.location.href = nextUrl;
    ui.emit("popstate");
    await ui.resolve(2, { heading: "Artikel Nutrisi." });
    assert.equal(ui.scrolls.at(-1).top, 700);
    assert.equal(ui.content.current.heading, "Artikel Nutrisi.");
    assert.equal(ui.history.length, 1);
});

test("alignment uses one smooth scroll and holds layout until scrolling ends", async () => {
    const ui = page({ scrollY: 600 });
    ui.click("?kategori=Nutrisi");
    await ui.resolve(0, { heading: "Artikel Nutrisi." });
    assert.equal(ui.scrolls.length, 1);
    assert.equal(ui.scrolls[0].top, 500);
    assert.equal(ui.scrolls[0].behavior, "smooth");
    assert.equal(ui.section.style.minHeight, "900px");
    ui.emit("scrollend");
    assert.equal(ui.section.style.minHeight, "");
});

test("reduced-motion navigation aligns immediately", async () => {
    const ui = page({ reducedMotion: true, scrollY: 600 });
    ui.click("?kategori=Nutrisi");
    await ui.resolve(0, { heading: "Artikel Nutrisi." });
    assert.equal(ui.scrolls[0].behavior, "auto");
    assert.equal(ui.section.style.minHeight, "");
});

test("modified clicks, article links, and foreign URLs retain native navigation", () => {
    const ui = page();
    assert.equal(
        ui.click("?kategori=Nutrisi", ".category-tabs a", { ctrlKey: true }),
        false,
    );
    assert.equal(ui.click("/artikel/example", ".card-cover"), false);
    assert.equal(ui.click("https://example.test/?kategori=Nutrisi"), false);
    assert.equal(ui.requests.length, 0);
});

test("leaving the page cancels pending updates and restores native scroll restoration", async () => {
    const ui = page();
    ui.click("?kategori=Nutrisi");
    ui.emit("pagehide");
    assert.equal(ui.requests[0].options.signal.aborted, true);
    assert.equal(ui.window.history.scrollRestoration, "auto");
    await ui.resolve(0, { heading: "Late results" });
    assert.equal(ui.content.current.heading, "Artikel terbaru.");
    assert.equal(ui.history.length, 0);
    assert.equal(ui.attributes.has("aria-busy"), false);
    ui.emit("pageshow");
    assert.equal(ui.window.history.scrollRestoration, "manual");
});
