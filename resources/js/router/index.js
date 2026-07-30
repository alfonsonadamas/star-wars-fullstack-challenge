import { createRouter, createWebHistory } from 'vue-router';
import MoviesView from '../views/MoviesView.vue';
import MovieStarshipsView from '../views/MovieStarshipsView.vue';
import StarshipDetailView from '../views/StarshipDetailView.vue';
import SavedStarshipsView from '../views/SavedStarshipsView.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/movies' },
        { path: '/movies', name: 'movies', component: MoviesView },
        {
            path: '/movies/:movieId/starships',
            name: 'movie-starships',
            component: MovieStarshipsView,
        },
        {
            path: '/starships/:starshipId',
            name: 'starship-detail',
            component: StarshipDetailView,
        },
        {
            path: '/saved-starships',
            name: 'saved-starships',
            component: SavedStarshipsView,
        },
        { path: '/:pathMatch(.*)*', redirect: '/movies' },
    ],
    scrollBehavior: () => ({ top: 0 }),
});

export default router;
