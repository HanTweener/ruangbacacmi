"use strict";

const articleJump = document.querySelector('.hero a[href="#artikel"]');
const articleSection = document.getElementById("artikel");

if (articleJump && articleSection) {
    articleJump.addEventListener("click", (event) => {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey
        )
            return;

        event.preventDefault();
        if (window.location.hash !== "#artikel") {
            window.history.pushState(null, "", "#artikel");
        }
        articleSection.scrollIntoView({
            behavior: window.matchMedia("(prefers-reduced-motion: reduce)")
                .matches
                ? "auto"
                : "smooth",
            block: "start",
        });
    });
}

const articleNavigation = document.querySelector("[data-article-navigation]");
if (articleNavigation) enhanceArticleNavigation(articleNavigation);

function enhanceArticleNavigation(section) {
    const endpoint = new URL(
        section.dataset.articleNavigation,
        window.location.href,
    );
    const content = section.querySelector("[data-article-content]");
    const status = section.querySelector("[data-article-status]");
    const error = section.querySelector("[data-article-error]");
    const retry = section.querySelector("[data-article-retry]");
    const positions = new Map([[window.location.href, window.scrollY]]);
    const originalRestoration = window.history.scrollRestoration;
    let controller;
    let requestId = 0;
    let restoring = false;
    let releaseLayout = () => {};

    window.history.scrollRestoration = "manual";
    window.addEventListener("pagehide", () => {
        requestId++;
        controller?.abort();
        releaseLayout();
        section.removeAttribute("aria-busy");
        status.textContent = "";
        restoring = false;
        window.history.scrollRestoration = originalRestoration;
    });
    window.addEventListener("pageshow", () => {
        window.history.scrollRestoration = "manual";
    });
    window.addEventListener(
        "scroll",
        () => {
            if (!restoring) positions.set(window.location.href, window.scrollY);
        },
        { passive: true },
    );

    const isArticleUrl = (url) =>
        url.origin === endpoint.origin && url.pathname === endpoint.pathname;
    const sectionPosition = () =>
        Math.max(
            0,
            window.scrollY +
                section.getBoundingClientRect().top -
                (parseFloat(
                    window.getComputedStyle(document.documentElement)
                        .scrollPaddingTop,
                ) || 0),
        );

    async function loadArticles(url, { pop = false, focus = null } = {}) {
        controller?.abort();
        controller = new AbortController();
        const currentRequest = ++requestId;
        const restorePosition =
            positions.get(url.href) ??
            (url.hash === "#artikel" ? sectionPosition() : 0);
        restoring = pop;
        section.setAttribute("aria-busy", "true");
        status.textContent = "Memuat artikel...";
        error.hidden = true;
        retry.href = url.href;

        try {
            const response = await fetch(url.href, {
                signal: controller.signal,
                credentials: "same-origin",
                headers: { Accept: "text/html" },
            });
            if (!response.ok || response.redirected)
                throw new Error("Article request failed");
            const html = await response.text();
            if (currentRequest !== requestId) return;
            const parsed = new DOMParser().parseFromString(html, "text/html");
            const replacement = parsed.querySelector("[data-article-content]");
            if (!replacement?.querySelector("h2"))
                throw new Error("Missing article content");

            // Retain the current height until scrolling settles, so shorter results
            // cannot clamp the viewport before the intended scroll begins.
            releaseLayout();
            const previousMinHeight = section.style.minHeight;
            section.style.minHeight = `${section.getBoundingClientRect().height}px`;
            let timer;
            releaseLayout = () => {
                window.clearTimeout(timer);
                window.removeEventListener("scrollend", releaseLayout);
                section.style.minHeight = previousMinHeight;
            };
            content.replaceChildren(...replacement.childNodes);
            if (!pop && url.href !== window.location.href) {
                positions.set(window.location.href, window.scrollY);
                window.history.pushState(null, "", url.href);
            }

            const target = pop ? restorePosition : sectionPosition();
            const reducedMotion = window.matchMedia(
                "(prefers-reduced-motion: reduce)",
            ).matches;
            const smooth =
                !pop && !reducedMotion && Math.abs(window.scrollY - target) > 2;
            if (smooth) {
                window.addEventListener("scrollend", releaseLayout, {
                    once: true,
                });
                timer = window.setTimeout(releaseLayout, 1200);
            }
            if (Math.abs(window.scrollY - target) > 2) {
                window.scrollTo({
                    top: target,
                    behavior: smooth ? "smooth" : "auto",
                });
            }
            if (!smooth) releaseLayout();
            if (focus)
                content.querySelector(focus)?.focus({ preventScroll: true });
            status.textContent = `${content.querySelector("h2").textContent} ${content.querySelectorAll(".article-card").length} artikel ditampilkan.`;
        } catch (failure) {
            if (currentRequest !== requestId || failure.name === "AbortError")
                return;
            error.hidden = false;
            status.textContent = "";
        } finally {
            if (currentRequest === requestId) {
                section.removeAttribute("aria-busy");
                restoring = false;
            }
        }
    }

    section.addEventListener("click", (event) => {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey
        )
            return;
        const link = event.target.closest("a[href]");
        if (
            !link ||
            link.target ||
            link.hasAttribute("download") ||
            !link.matches(
                ".category-tabs a, .pagination a, [data-article-reset], [data-article-retry]",
            )
        )
            return;
        const url = new URL(link.href, window.location.href);
        if (!isArticleUrl(url)) return;
        url.hash = "artikel";
        event.preventDefault();
        loadArticles(url, {
            focus: link.matches(".category-tabs a")
                ? ".category-tabs .selected"
                : "h2",
        });
    });

    section.addEventListener("submit", (event) => {
        const form = event.target;
        if (!form.matches(".search-form") || event.defaultPrevented) return;
        const url = new URL(form.action, window.location.href);
        if (!isArticleUrl(url)) return;
        url.search = new URLSearchParams(new FormData(form)).toString();
        url.hash = "artikel";
        event.preventDefault();
        loadArticles(url, { focus: "#public-search" });
    });

    window.addEventListener("popstate", () => {
        const url = new URL(window.location.href);
        if (isArticleUrl(url)) loadArticles(url, { pop: true });
    });
}

const menu = document.querySelector(".menu-toggle");
menu?.addEventListener("click", () => {
    const expanded = menu.getAttribute("aria-expanded") !== "true";
    menu.setAttribute("aria-expanded", String(expanded));
    document.getElementById("main-nav").classList.toggle("open", expanded);
});
document.querySelectorAll("[data-password]").forEach((button) => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.dataset.password);
        const reveal = input.type === "password";
        input.type = reveal ? "text" : "password";
        button.textContent = reveal ? "Tutup" : "Lihat";
        button.setAttribute("aria-pressed", String(reveal));
        button.setAttribute(
            "aria-label",
            reveal ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
        );
    });
});
const dialog = document.getElementById("delete-dialog");
document.querySelectorAll("[data-delete]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (form.dataset.confirmed) return;
        event.preventDefault();
        dialog.returnValue = "";
        dialog.showModal();
        dialog.addEventListener(
            "close",
            () => {
                if (dialog.returnValue === "confirm") {
                    form.dataset.confirmed = "true";
                    form.requestSubmit();
                }
            },
            { once: true },
        );
    });
});
const imageInput = document.getElementById("gambar");
const imagePreview = document.getElementById("image-preview");
const imageError = document.getElementById("image-error");
const originalPreview = imagePreview?.getAttribute("src");
let previewUrl;
let imageSelection = 0;
imageInput?.addEventListener("change", () => {
    const selection = ++imageSelection;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = undefined;
    imageInput.setCustomValidity("");
    imageInput.removeAttribute("aria-invalid");
    imageError.textContent = "";
    imageError.hidden = true;
    if (originalPreview) imagePreview.src = originalPreview;
    else imagePreview.removeAttribute("src");
    imagePreview.hidden = !originalPreview;
    const file = imageInput.files[0];
    if (!file) return;

    const rejectImage = (message) => {
        imageInput.setCustomValidity(message);
        imageInput.setAttribute("aria-invalid", "true");
        imageError.textContent = message;
        imageError.hidden = false;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = undefined;
    };
    const maxBytes = Number(imageInput.dataset.maxBytes);
    const maxWidth = Number(imageInput.dataset.maxWidth);
    const maxHeight = Number(imageInput.dataset.maxHeight);
    if (file.size > maxBytes) {
        rejectImage(
            `Ukuran gambar ${(file.size / 1048576).toFixed(2).replace(".", ",")} MB; maksimal 2 MB. Kompres gambar atau pilih file yang lebih kecil.`,
        );
        return;
    }
    if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
        rejectImage("Gunakan gambar JPG/JPEG, PNG, atau WebP.");
        return;
    }
    imageInput.setCustomValidity(
        "Pemeriksaan gambar sedang berlangsung. Coba simpan setelah pratinjau muncul.",
    );
    previewUrl = URL.createObjectURL(file);
    const candidateUrl = previewUrl;
    const candidate = new Image();
    candidate.onload = () => {
        if (selection !== imageSelection) return;
        if (
            candidate.naturalWidth > maxWidth ||
            candidate.naturalHeight > maxHeight
        ) {
            rejectImage(
                `Dimensi gambar ${candidate.naturalWidth} × ${candidate.naturalHeight} piksel; maksimal ${maxWidth} × ${maxHeight} piksel. Perkecil gambar sebelum mengunggah.`,
            );
            return;
        }
        imageInput.setCustomValidity("");
        imagePreview.src = candidateUrl;
        imagePreview.hidden = false;
    };
    candidate.onerror = () => {
        if (selection === imageSelection)
            rejectImage(
                "Gambar tidak dapat dibaca. Pilih file JPG/JPEG, PNG, atau WebP yang valid.",
            );
    };
    candidate.src = candidateUrl;
});
document
    .querySelectorAll('form[method="post"]:not([data-delete])')
    .forEach((form) => {
        form.addEventListener("submit", () => {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.textContent = "Memproses...";
            }
        });
    });
