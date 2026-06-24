# Design System Documentation: Digital Culinary Precision

## 1. Overview & Creative North Star
**Creative North Star: The Architectural Sous-Chef**
This design system is built on the philosophy of "Architectural Sous-Chef"—a visual language that is as disciplined and organized as a professional kitchen, yet as fluid as the service itself. We are moving away from the "generic SaaS dashboard" look. Instead of boxing data into rigid containers, we use tonal depth and intentional white space to guide the eye.

The system utilizes **Helvetica**—the gold standard of neutral, Swiss objectivity—to let the restaurant’s data speak for itself. We achieve a premium feel not through decorative elements, but through the rigorous application of a "No-Line" philosophy and sophisticated surface nesting.

---

## 2. Colors & Surface Logic
The palette transitions from the deep, authoritative `primary` (#003f48) to the vibrant, fresh `secondary` (#006d3e). This creates a high-contrast environment that feels clinical yet organic.

### The "No-Line" Rule
To achieve a signature, high-end look, **do not use 1px solid borders for sectioning.** Boundaries must be defined solely through background color shifts.
* **Example:** A `surface_container_low` sidebar sitting against a `background` page.
* **The Goal:** Eliminate visual "noise" and friction, allowing the content to breathe.

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers stacked on a workbench. Use the surface-container tiers to define importance:
* **Background (#f5fafa):** The base canvas.
* **Surface Container Low (#eff5f5):** Large layout sections (e.g., secondary navigation).
* **Surface Container (#e9efef):** Main content areas.
* **Surface Container Highest (#dee4e4):** Active states or high-priority floating elements.
* **Surface Container Lowest (#ffffff):** The "Hero Card"—reserved for the most critical data points to provide maximum contrast.

---

## 3. Typography
We utilize Helvetica across a strict editorial scale to convey authority and clarity.

* **Display (3.5rem - 2.25rem):** Used for top-level restaurant performance metrics. Heavy weights (Bold) create a sense of scale.
* **Headline (2rem - 1.5rem):** Used for section titles (e.g., "Live Orders," "Inventory Alerts"). Use `on_surface` (#171d1d) to ensure maximum legibility.
* **Title (1.375rem - 1rem):** SemiBold. Used for card headers and modal titles.
* **Body (1rem - 0.75rem):** Regular. The workhorse for data tables and descriptions.
* **Label (0.75rem - 0.6875rem):** Medium. Reserved for micro-copy, table headers, and status tags.

---

## 4. Elevation & Depth
Depth is achieved through **Tonal Layering** rather than traditional drop shadows.

### The Layering Principle
To create "lift," place a `surface_container_lowest` (#ffffff) card on a `surface_container_low` (#eff5f5) background. This creates a soft, natural edge that feels integrated into the environment.

### Ambient Shadows
When a component must float (e.g., a dropdown or a floating action button), use an extra-diffused shadow:
* **Value:** `0 8px 32px rgba(2, 88, 100, 0.06)`
* **Note:** The shadow is tinted with our `primary` teal to mimic natural light refraction.

### The "Ghost Border" Fallback
If an edge must be defined for accessibility, use the `outline_variant` (#bfc8ca) at **20% opacity**. This creates a suggestion of a boundary without the harshness of a solid line.

---

## 5. Components

### Buttons
* **Primary:** `rounded-full`, Background: `primary` (#003f48), Text: `on_primary` (#ffffff).
* **Secondary:** `rounded-full`, Background: `secondary_container` (#52fca1), Text: `on_secondary_container` (#007241).
* **Tertiary:** `rounded-full`, No background. Text: `primary`. High-density padding: `spacing-2` (0.4rem) vertical, `spacing-5` (1.1rem) horizontal.

### Cards & Lists
* **Constraint:** Absolutely no divider lines between list items.
* **Execution:** Use `spacing-3` (0.6rem) to `spacing-4` (0.9rem) of vertical white space to separate items.
* **Radius:** All cards must use `rounded-md` (0.75rem / 12px) to maintain a soft-modernist aesthetic.

### Input Fields
* **Style:** Minimalist. No bottom border or full box. Use a subtle `surface_container` background with a `rounded-sm` (0.25rem) radius.
* **Active State:** Shift background to `surface_container_highest` and add a 2px `primary` indicator on the left edge of the input.

### Status Chips
* **Success:** `secondary_fixed` background with `on_secondary_fixed_variant` text.
* **Warning/Danger:** Use `tertiary_fixed` and `error_container` respectively. These should be small, high-contrast labels using `label-sm`.

---

## 6. Do’s and Don’ts

### Do:
* **Do** use asymmetrical layouts for the main dashboard (e.g., a wide 8-column main feed with a 4-column "Live Kitchen Stats" sidebar).
* **Do** use `spacing-10` (2.25rem) for major section breathing room.
* **Do** nest cards within containers to create a "Russian Doll" depth effect.

### Don’t:
* **Don’t** use pure black (#000000) for text. Use `on_surface` (#171d1d) to maintain the cool teal-gray undertone.
* **Don’t** use 100% opaque borders. They clutter the dense data environment.
* **Don’t** use gradients. The visual interest must come from the contrast between different `surface` tiers and the vibrant `accent` mint.
* **Don’t** center-align data. In a kitchen admin context, left-aligned density is superior for rapid scanning.
