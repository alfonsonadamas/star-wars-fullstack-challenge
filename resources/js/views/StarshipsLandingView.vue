<script setup>
import AppBreadcrumbs from "../components/AppBreadcrumbs.vue";
</script>

<template>
    <AppBreadcrumbs :items="[{ label: 'Catálogo' }, { label: 'Naves' }]" />

    <header class="mt-12">
        <h1 class="page-title">Naves</h1>
        <p class="page-description">
            Las naves se consultan según la película en la que aparecen.
        </p>
    </header>

    <section
        class="panel mt-9 flex min-h-96 flex-col items-center justify-center px-6 py-14 text-center"
        aria-labelledby="starships-empty-title"
    >
        <div
            class="flex size-16 items-center justify-center rounded-full bg-star-soft text-3xl text-star"
            aria-hidden="true"
        >
            ✦
        </div>
        <h2
            id="starships-empty-title"
            class="mt-6 text-2xl font-semibold text-white"
        >
            Selecciona una película
        </h2>
        <p class="mt-3 max-w-lg leading-7 text-muted">
            Ve a la sección Películas y elige “Ver naves” para consultar las
            naves que aparecen en ese episodio.
        </p>
        <RouterLink class="btn-primary mt-7" :to="{ name: 'movies' }">
            Ir a películas
        </RouterLink>
    </section>
</template>
