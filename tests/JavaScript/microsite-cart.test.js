import assert from "node:assert/strict";
import test from "node:test";

import { registerMicrositeCart } from "../../resources/js/microsite/cart.js";

const CART_STORAGE_KEY = "microsite_cart_v1";

function createLocalStorage(initialEntries = {}) {
    const storage = new Map(Object.entries(initialEntries));

    return {
        getItem(key) {
            return storage.has(key) ? storage.get(key) : null;
        },

        setItem(key, value) {
            storage.set(key, String(value));
        },

        removeItem(key) {
            storage.delete(key);
        },

        clear() {
            storage.clear();
        },
    };
}

function bootCart(initialCart = { items: {} }) {
    global.localStorage = createLocalStorage({
        [CART_STORAGE_KEY]: JSON.stringify(initialCart),
    });

    const stores = {};
    const Alpine = {
        store(name, value) {
            stores[name] = value;
            return value;
        },

        data() {},
    };

    registerMicrositeCart(Alpine);

    const cart = stores.micrositeCart;
    cart.init();

    return {
        cart,
        readPersistedCart() {
            return JSON.parse(global.localStorage.getItem(CART_STORAGE_KEY) ?? "{}");
        },
    };
}

test("increment creates a selected item immediately from zero quantity", () => {
    const { cart, readPersistedCart } = bootCart();

    cart.increment(42, "Nasi Goreng", 25000, "/images/nasi-goreng.jpg");

    assert.equal(cart.quantity(42), 1);
    assert.deepEqual(cart.items["42"], {
        id: "42",
        name: "Nasi Goreng",
        unitPrice: 25000,
        image: "/images/nasi-goreng.jpg",
        quantity: 1,
    });
    assert.deepEqual(readPersistedCart().items["42"], cart.items["42"]);
});

test("increment syncs persisted cart quantity for existing selected items", () => {
    const { cart, readPersistedCart } = bootCart({
        items: {
            42: {
                id: "42",
                name: "Nasi Goreng",
                unitPrice: 25000,
                image: "/images/nasi-goreng.jpg",
                quantity: 2,
            },
        },
    });

    cart.increment(42, "Nasi Goreng", 25000, "/images/nasi-goreng.jpg");

    assert.equal(cart.quantity(42), 3);
    assert.equal(cart.items["42"].quantity, 3);
    assert.equal(readPersistedCart().items["42"].quantity, 3);
});

test("decrement ignores items that have not been selected yet", () => {
    const { cart, readPersistedCart } = bootCart();

    cart.decrement(7, "Es Kopi", 18000, "/images/es-kopi.jpg");

    assert.equal(cart.quantity(7), 0);
    assert.equal(cart.items["7"], undefined);
    assert.equal(readPersistedCart().items["7"], undefined);
});

test("decrement syncs persisted cart quantity and removes the item at zero", () => {
    const { cart, readPersistedCart } = bootCart({
        items: {
            7: {
                id: "7",
                name: "Es Kopi",
                unitPrice: 18000,
                image: "/images/es-kopi.jpg",
                quantity: 1,
            },
        },
    });

    cart.decrement(7, "Es Kopi");

    assert.equal(cart.quantity(7), 0);
    assert.equal(cart.items["7"], undefined);
    assert.equal(readPersistedCart().items["7"], undefined);
    assert.equal(cart.totalItems(), 0);
});

test("subtotal remains the final payable amount without tax", () => {
    const { cart } = bootCart({
        items: {
            7: {
                id: "7",
                name: "Es Kopi",
                unitPrice: 18000,
                image: "/images/es-kopi.jpg",
                quantity: 2,
            },
            42: {
                id: "42",
                name: "Nasi Goreng",
                unitPrice: 25000,
                image: "/images/nasi-goreng.jpg",
                quantity: 1,
            },
        },
    });

    assert.equal(cart.subtotal(), 61000);
    assert.equal(cart.formatRupiah(cart.subtotal()), "Rp 61.000");
});
