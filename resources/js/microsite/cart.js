const CART_STORAGE_KEY = "microsite_cart_v1";

function readCart() {
    try {
        const parsed = JSON.parse(localStorage.getItem(CART_STORAGE_KEY) ?? "{}");
        return parsed && typeof parsed === "object" && parsed.items ? parsed : { items: {} };
    } catch {
        return { items: {} };
    }
}

function writeCart(cart) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
}

export function registerMicrositeCart(Alpine) {
    Alpine.store("micrositeCart", {
        items: {},
        draftQuantities: {},
        initialized: false,

        normalizeItems(rawItems) {
            return Object.entries(rawItems ?? {}).reduce((normalized, [id, item]) => {
                const normalizedId = String(id);
                const unitPrice = Number(
                    item?.unitPrice ?? item?.price ?? item?.priceValue ?? item?.unit_price ?? 0,
                );
                const quantity = Math.max(0, Number(item?.quantity ?? 0));

                if (quantity <= 0) {
                    return normalized;
                }

                normalized[normalizedId] = {
                    id: normalizedId,
                    name: item?.name ?? "Menu Item",
                    unitPrice: Number.isFinite(unitPrice) ? unitPrice : 0,
                    image: item?.image ?? "",
                    quantity,
                };

                return normalized;
            }, {});
        },

        init() {
            if (this.initialized) {
                return;
            }

            const cart = readCart();
            this.items = this.normalizeItems(cart.items);
            this.draftQuantities = Object.entries(this.items).reduce((draft, [id, item]) => {
                draft[id] = Number(item.quantity ?? 0);
                return draft;
            }, {});
            this.persist();
            this.initialized = true;
        },

        persist() {
            writeCart({ items: this.items });
        },

        quantity(itemId) {
            return Number(this.draftQuantities[String(itemId)] ?? 0);
        },

        setDraftQuantity(itemId, quantity) {
            const normalizedId = String(itemId);
            this.draftQuantities[normalizedId] = Math.max(0, Number(quantity ?? 0));
        },

        setQuantity(itemId, itemName, quantity, unitPrice = null, image = null) {
            const normalizedId = String(itemId);
            const currentItem = this.items[normalizedId] ?? {};
            const normalizedQuantity = Math.max(0, Number(quantity ?? 0));

            this.setDraftQuantity(normalizedId, normalizedQuantity);

            if (normalizedQuantity <= 0) {
                delete this.items[normalizedId];
            } else {
                this.items[normalizedId] = {
                    id: normalizedId,
                    name: itemName ?? currentItem.name ?? "Menu Item",
                    unitPrice: Number(unitPrice ?? currentItem.unitPrice ?? 0),
                    image: image ?? currentItem.image ?? "",
                    quantity: normalizedQuantity,
                };
            }

            this.persist();
        },

        increment(itemId, itemName, unitPrice = null, image = null) {
            const normalizedId = String(itemId);
            const nextQuantity = this.quantity(normalizedId) + 1;

            this.setQuantity(normalizedId, itemName, nextQuantity, unitPrice, image);
        },

        decrement(itemId, itemName, unitPrice = null, image = null) {
            const normalizedId = String(itemId);
            const currentItem = this.items[normalizedId];

            if (!currentItem) {
                this.setDraftQuantity(normalizedId, 0);
                return;
            }

            const nextQuantity = Number(currentItem.quantity ?? 0) - 1;
            this.setQuantity(normalizedId, itemName, nextQuantity, unitPrice, image);
        },

        totalItems() {
            return Object.values(this.items).reduce((sum, item) => {
                return sum + Number(item.quantity ?? 0);
            }, 0);
        },

        cartItems() {
            return Object.values(this.items);
        },

        incrementCartItem(itemId) {
            const normalizedId = String(itemId);
            const currentItem = this.items[normalizedId];
            if (!currentItem) {
                return;
            }

            const nextQuantity = Number(currentItem.quantity ?? 0) + 1;
            this.setQuantity(
                normalizedId,
                currentItem.name,
                nextQuantity,
                currentItem.unitPrice,
                currentItem.image,
            );
        },

        decrementCartItem(itemId) {
            const normalizedId = String(itemId);
            const currentItem = this.items[normalizedId];
            if (!currentItem) {
                return;
            }

            const nextQuantity = Math.max(0, Number(currentItem.quantity ?? 0) - 1);
            this.setQuantity(normalizedId, currentItem.name, nextQuantity);
        },

        remove(itemId) {
            const normalizedId = String(itemId);
            delete this.items[normalizedId];
            this.setDraftQuantity(normalizedId, 0);
            this.persist();
        },

        clear() {
            this.items = {};
            this.draftQuantities = {};
            this.persist();
        },

        subtotal() {
            return this.cartItems().reduce((sum, item) => {
                return sum + Number(item.unitPrice ?? 0) * Number(item.quantity ?? 0);
            }, 0);
        },

        formatRupiah(amount) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                maximumFractionDigits: 0,
            }).format(Number(amount ?? 0));
        },

        selectedLabel() {
            return `${this.totalItems()} item selected`;
        },
    });

    Alpine.data("micrositeCartPage", () => ({
        init() {
            this.$store.micrositeCart.init();
        },
    }));
}
