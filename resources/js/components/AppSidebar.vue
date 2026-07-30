<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["navigate"]);

const route = useRoute();

const items = [
    { label: "Películas", to: "/movies", section: "movies" },
    { label: "Naves", to: "/starships", section: "starships" },
    { label: "Naves guardadas", to: "/saved-starships", section: "saved" },
];

const currentSection = computed(() => {
    if (route.path.startsWith("/saved-starships")) return "saved";
    if (
        route.path.includes("/starships") ||
        route.path.startsWith("/starships")
    )
        return "starships";
    return "movies";
});
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-40 w-72 border-r border-white/3 bg-space-900 px-8 py-12 transition-transform duration-200 lg:translate-x-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <RouterLink to="/movies" class="block" @click="$emit('navigate')">
            <span
                class="block text-2xl font-bold tracking-[0.04em] text-white uppercase"
                >El Rincon del Mandalorian</span
            >
            <span
                class="mt-4 block text-xs font-semibold tracking-[0.16em] text-star uppercase"
                >Star Wars Data</span
            >
        </RouterLink>

        <nav class="mt-20 space-y-4" aria-label="Navegación principal">
            <RouterLink
                v-for="item in items"
                :key="item.section"
                :to="item.to"
                class="block rounded-xl px-5 py-4 font-medium transition"
                :class="
                    currentSection === item.section
                        ? 'bg-star-soft text-star'
                        : 'text-muted hover:bg-white/3 hover:text-white'
                "
                @click="$emit('navigate')"
            >
                {{ item.label }}
            </RouterLink>
        </nav>
    </aside>
</template>
