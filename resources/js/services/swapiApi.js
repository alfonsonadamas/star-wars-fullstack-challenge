import http from './http';

export const swapiApi = {
    async getFilms() {
        const response = await http.get('/swapi/films');
        return response.data.data;
    },

    async getFilmStarships(filmId) {
        const response = await http.get(`/swapi/films/${filmId}/starships`);
        return response.data.data;
    },

    async getStarship(starshipId) {
        const response = await http.get(`/swapi/starships/${starshipId}`);
        return response.data.data;
    },
};
