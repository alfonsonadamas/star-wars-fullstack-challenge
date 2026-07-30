<script setup>
import { computed, onMounted, ref } from "vue";
import AppBreadcrumbs from "../components/AppBreadcrumbs.vue";
import ConfirmDialog from "../components/ConfirmDialog.vue";
import { errorMessage } from "../services/http";
import { starshipApi } from "../services/starshipApi";

const records = ref([]);
const search = ref("");
const loading = ref(true);
const error = ref("");
const deleting = ref(false);
const pendingDelete = ref(null);

const filteredRecords = computed(() => {
    const term = search.value.trim().toLowerCase();
    return term
        ? records.value.filter((item) => item.name.toLowerCase().includes(term))
        : records.value;
});

function formattedDate(value) {
    return new Intl.DateTimeFormat("es-MX", { dateStyle: "medium" }).format(
        new Date(value),
    );
}

async function loadRecords() {
    loading.value = true;
    error.value = "";

    try {
        const response = await starshipApi.getAll();
        records.value = response.data;
    } catch (requestError) {
        error.value = errorMessage(
            requestError,
            "No fue posible cargar las naves guardadas.",
        );
    } finally {
        loading.value = false;
    }
}

async function removeRecord(id) {
    deleting.value = true;
    try {
        await starshipApi.remove(id);
        records.value = records.value.filter((item) => item.id !== id);
        pendingDelete.value = null;
    } catch (requestError) {
        error.value = errorMessage(
            requestError,
            "No fue posible eliminar la nave.",
        );
    } finally {
        deleting.value = false;
    }
}

onMounted(loadRecords);
</script>

<template>
    <AppBreadcrumbs
        :items="[{ label: 'Colección' }, { label: 'Naves guardadas' }]"
    />

    <header class="mt-12">
        <h1 class="page-title">Naves guardadas</h1>
        <p class="page-description">
            Consulta y administra las naves almacenadas mediante tu API local.
        </p>
    </header>

    <div
        class="mt-9 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <label class="relative block w-full max-w-md">
            <span class="sr-only">Buscar nave guardada</span>
            <span
                class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-muted"
                aria-hidden="true"
                >⌕</span
            >
            <input
                v-model="search"
                class="field pl-11"
                type="search"
                placeholder="Buscar nave guardada"
            />
        </label>
        <span
            class="self-start rounded-full bg-star-soft px-7 py-3 text-sm font-semibold text-star sm:self-auto"
        >
            {{ records.length }} registros
        </span>
    </div>

    <div v-if="loading" class="panel mt-9 px-7 py-16 text-center text-muted">
        Cargando naves guardadas…
    </div>

    <div
        v-else-if="error && records.length === 0"
        class="panel mt-9 px-7 py-12 text-center"
    >
        <p class="text-danger">{{ error }}</p>
        <button class="btn-secondary mt-5" type="button" @click="loadRecords">
            Reintentar
        </button>
    </div>

    <section
        v-else
        class="mt-9 grid gap-6 md:grid-cols-2 xl:grid-cols-3"
        aria-label="Naves guardadas"
    >
        <p
            v-if="error"
            class="col-span-full rounded-xl border border-danger/30 bg-red-950/30 p-4 text-danger"
        >
            {{ error }}
        </p>
        <article
            v-for="record in filteredRecords"
            :key="record.id"
            class="panel flex flex-col p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <span
                    class="rounded-full bg-emerald-950 px-4 py-2 text-xs font-semibold text-success"
                    >Guardada</span
                >
                <span class="text-sm text-muted"
                    >#SW-{{ String(record.id).padStart(4, "0") }}</span
                >
            </div>
            <h2 class="mt-6 text-2xl font-semibold">{{ record.name }}</h2>
            <dl
                class="mt-6 grid grid-cols-2 gap-4 border-y border-space-700 py-5"
            >
                <div>
                    <dt class="text-xs text-muted uppercase">Velocidad</dt>
                    <dd class="mt-2">
                        {{ record.max_atmosphering_speed }} km/h
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-muted uppercase">Carga</dt>
                    <dd class="mt-2">{{ record.cargo_capacity }} kg</dd>
                </div>
            </dl>
            <p class="mt-4 text-sm text-muted">
                Actualizada: {{ formattedDate(record.updated_at) }}
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <RouterLink
                    class="btn-primary min-h-11 px-5"
                    :to="{
                        name: 'starship-detail',
                        params: { starshipId: record.swapi_id },
                    }"
                >
                    Editar
                </RouterLink>
                <button
                    class="btn-danger min-h-11 px-5"
                    type="button"
                    @click="pendingDelete = record"
                >
                    Eliminar
                </button>
            </div>
        </article>

        <div
            v-if="filteredRecords.length === 0"
            class="panel col-span-full py-16 text-center text-muted"
        >
            No hay naves guardadas con ese criterio.
        </div>
    </section>

    <ConfirmDialog
        :open="Boolean(pendingDelete)"
        title="Eliminar nave guardada"
        :message="`Esta acción eliminará ${pendingDelete?.name ?? 'esta nave'} de forma permanente.`"
        :loading="deleting"
        @cancel="pendingDelete = null"
        @confirm="removeRecord(pendingDelete.id)"
    />
</template>
