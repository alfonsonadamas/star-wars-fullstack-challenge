<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import AppBreadcrumbs from "../components/AppBreadcrumbs.vue";
import { errorMessage } from "../services/http";
import { swapiApi } from "../services/swapiApi";

const route = useRoute();
const movie = ref(null);
const starships = ref([]);
const loading = ref(true);
const error = ref("");

const romanEpisodes = ["I", "II", "III", "IV", "V", "VI", "VII"];

function episodeLabel(episodeId) {
    return romanEpisodes[episodeId - 1] ?? episodeId;
}

function releaseYear(releaseDate) {
    return new Date(`${releaseDate}T00:00:00`).getFullYear();
}

async function loadStarships() {
    loading.value = true;
    error.value = "";

    try {
        const data = await swapiApi.getFilmStarships(route.params.movieId);
        movie.value = data.film;
        starships.value = data.starships;
    } catch (requestError) {
        error.value = errorMessage(
            requestError,
            "No fue posible cargar las naves de esta película.",
        );
    } finally {
        loading.value = false;
    }
}

onMounted(loadStarships);
</script>

<template>
    <AppBreadcrumbs
        :items="[
            { label: 'Películas', to: '/movies' },
            { label: movie?.title ?? 'Película' },
            { label: 'Naves' },
        ]"
    />

    <RouterLink class="btn-secondary mt-9 min-h-11 px-4" to="/movies"
        >← Volver</RouterLink
    >

    <div v-if="loading" class="panel mt-10 px-7 py-16 text-center text-muted">
        Cargando naves…
    </div>

    <div v-else-if="error" class="panel mt-10 px-7 py-12 text-center">
        <p class="text-danger">{{ error }}</p>
        <button class="btn-secondary mt-5" type="button" @click="loadStarships">
            Reintentar
        </button>
    </div>

    <template v-else>
        <header class="mt-10">
            <h1 class="page-title">Naves en {{ movie.title }}</h1>
            <p class="page-description">
                Selecciona una nave para revisar sus especificaciones
                principales y guardar una copia editable.
            </p>
        </header>

        <div
            class="mt-8 flex flex-wrap gap-x-12 gap-y-3 rounded-2xl bg-star-soft px-6 py-5 text-sm"
        >
            <strong class="text-star uppercase"
                >Episodio {{ episodeLabel(movie.episode_id) }}</strong
            >
            <span class="text-gray-200">{{ movie.director }}</span>
            <span class="text-muted"
                >Estreno · {{ releaseYear(movie.release_date) }}</span
            >
            <strong class="text-star">{{ starships.length }} naves</strong>
        </div>

        <section
            class="mt-9 grid gap-6 md:grid-cols-2 xl:grid-cols-3"
            aria-label="Naves de la película"
        >
            <RouterLink
                v-for="starship in starships"
                :key="starship.id"
                :to="{
                    name: 'starship-detail',
                    params: { starshipId: starship.id },
                    query: { film: movie.id },
                }"
                class="panel group flex min-h-64 flex-col p-6 transition hover:-translate-y-1 hover:border-star/60 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-star"
            >
                <span
                    class="w-28 rounded-full bg-star-soft px-4 py-2 text-center text-xs font-semibold text-star uppercase"
                >
                    Nave
                </span>
                <h2 class="mt-5 text-2xl font-semibold text-gray-100">
                    {{ starship.name }}
                </h2>
                <p class="mt-3 text-muted">{{ starship.manufacturer }}</p>
                <div
                    class="mt-auto flex items-center justify-between gap-4 border-t border-space-700 pt-4"
                >
                    <span class="text-muted">{{
                        ["n/a", "unknown"].includes(
                            starship.max_atmosphering_speed,
                        )
                            ? "No disponible"
                            : `${starship.max_atmosphering_speed} km/h`
                    }}</span>
                    <span
                        class="btn-primary min-h-11 px-5 group-hover:bg-yellow-300"
                        >Ver detalle</span
                    >
                </div>
            </RouterLink>
        </section>
    </template>
</template>
