<script setup>
import { ref } from 'vue';
import AppSidebar from '../components/AppSidebar.vue';

const menuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-space-950">
        <button
            class="fixed top-4 right-4 z-50 flex size-11 items-center justify-center rounded-xl border border-space-700 bg-space-850 text-xl text-white lg:hidden"
            type="button"
            aria-label="Abrir navegación"
            :aria-expanded="menuOpen"
            @click="menuOpen = !menuOpen"
        >
            {{ menuOpen ? '×' : '☰' }}
        </button>

        <AppSidebar :open="menuOpen" @navigate="menuOpen = false" />

        <div
            v-if="menuOpen"
            class="fixed inset-0 z-30 bg-black/70 lg:hidden"
            aria-hidden="true"
            @click="menuOpen = false"
        />

        <main class="min-h-screen px-5 py-20 sm:px-8 lg:ml-72 lg:px-16 lg:py-16 xl:px-20">
            <div class="mx-auto max-w-[1400px]">
                <slot />
            </div>
        </main>
    </div>
</template>
