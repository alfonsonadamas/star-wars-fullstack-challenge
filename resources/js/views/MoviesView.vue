<script setup>
import { computed, onMounted, ref } from "vue";
import AppBreadcrumbs from "../components/AppBreadcrumbs.vue";
import { errorMessage } from "../services/http";
import { swapiApi } from "../services/swapiApi";

const search = ref("");
const movies = ref([]);
const loading = ref(true);
const error = ref("");

const filteredMovies = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return movies.value;

    return movies.value.filter((movie) =>
        [movie.title, movie.director].some((value) =>
            value.toLowerCase().includes(term),
        ),
    );
});

const romanEpisodes = ["I", "II", "III", "IV", "V", "VI", "VII"];

function episodeLabel(episodeId) {
    return romanEpisodes[episodeId - 1] ?? episodeId;
}

function releaseYear(releaseDate) {
    return new Date(`${releaseDate}T00:00:00`).getFullYear();
}

async function loadMovies() {
    loading.value = true;
    error.value = "";

    try {
        movies.value = await swapiApi.getFilms();
    } catch (requestError) {
        error.value = errorMessage(
            requestError,
            "No fue posible cargar las películas.",
        );
    } finally {
        loading.value = false;
    }
}

onMounted(loadMovies);
</script>

<template>
    <AppBreadcrumbs :items="[{ label: 'Catálogo' }, { label: 'Películas' }]" />

    <header class="mt-12">
        <h1 class="page-title">Películas de la galaxia</h1>
        <p class="page-description">
            Explora la saga, revisa sus datos principales y consulta las naves
            que aparecen en cada episodio.
        </p>
    </header>

    <div
        class="mt-9 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <label class="relative block w-full max-w-md">
            <span class="sr-only">Buscar por título o director</span>
            <span
                class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-muted"
                aria-hidden="true"
                >⌕</span
            >
            <input
                v-model="search"
                class="field pl-11"
                type="search"
                placeholder="Buscar por título o director"
            />
        </label>
        <span
            class="self-start rounded-full bg-star-soft px-7 py-3 text-sm font-semibold text-star sm:self-auto"
        >
            {{ filteredMovies.length }} películas cargadas
        </span>
    </div>

    <div v-if="loading" class="panel mt-9 px-7 py-16 text-center text-muted">
        Cargando películas de la galaxia…
    </div>

    <div v-else-if="error" class="panel mt-9 px-7 py-12 text-center">
        <p class="text-danger">{{ error }}</p>
        <button class="btn-secondary mt-5" type="button" @click="loadMovies">
            Reintentar
        </button>
    </div>

    <section
        v-else
        class="panel mt-9 overflow-hidden"
        aria-label="Listado de películas"
    >
        <div class="overflow-x-auto">
            <table class="w-full min-w-190 border-collapse text-left">
                <thead
                    class="bg-space-800 text-xs font-semibold text-muted uppercase"
                >
                    <tr>
                        <th class="px-7 py-6">Película</th>
                        <th class="px-7 py-6">Episodio</th>
                        <th class="px-7 py-6">Director</th>
                        <th class="px-7 py-6">Estreno</th>
                        <th class="px-7 py-6">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="movie in filteredMovies"
                        :key="movie.id"
                        class="border-t border-space-700 text-muted transition hover:bg-white/2"
                    >
                        <td class="px-7 py-5 font-semibold text-gray-100">
                            {{ movie.title }}
                        </td>
                        <td class="px-7 py-5">
                            {{ episodeLabel(movie.episode_id) }}
                        </td>
                        <td class="px-7 py-5">{{ movie.director }}</td>
                        <td class="px-7 py-5">
                            {{ releaseYear(movie.release_date) }}
                        </td>
                        <td class="px-7 py-3">
                            <RouterLink
                                class="btn-primary min-h-11 whitespace-nowrap px-5"
                                :to="{
                                    name: 'movie-starships',
                                    params: { movieId: movie.id },
                                }"
                            >
                                Ver naves
                            </RouterLink>
                        </td>
                    </tr>
                    <tr v-if="filteredMovies.length === 0">
                        <td
                            colspan="5"
                            class="px-7 py-16 text-center text-muted"
                        >
                            No encontramos películas con ese criterio.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
