<script setup>
defineProps({
    items: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <nav class="eyebrow flex flex-wrap items-center gap-3" aria-label="Migas de pan">
        <template v-for="(item, index) in items" :key="`${item.label}-${index}`">
            <RouterLink v-if="item.to" :to="item.to" class="hover:text-yellow-300">
                {{ item.label }}
            </RouterLink>
            <span v-else>{{ item.label }}</span>
            <span v-if="index < items.length - 1" aria-hidden="true">/</span>
        </template>
    </nav>
</template>
