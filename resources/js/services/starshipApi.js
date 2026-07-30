import http from "./http";

export const starshipApi = {
    async getAll(page = 1) {
        const response = await http.get("/starships", { params: { page } });
        return response.data;
    },

    async getBySwapiId(swapiId) {
        const response = await http.get("/starships", {
            params: { swapi_id: swapiId },
        });

        return response.data.data[0] ?? null;
    },

    async create(payload) {
        const response = await http.post("/starships", payload);
        return response.data.data;
    },

    async update(id, payload) {
        const response = await http.patch(`/starships/${id}`, payload);
        return response.data.data;
    },

    async remove(id) {
        await http.delete(`/starships/${id}`);
    },
};
