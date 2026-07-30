<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import AppBreadcrumbs from "../components/AppBreadcrumbs.vue";
import ConfirmDialog from "../components/ConfirmDialog.vue";
import { errorMessage } from "../services/http";
import { starshipApi } from "../services/starshipApi";
import { swapiApi } from "../services/swapiApi";

const route = useRoute();
const router = useRouter();
const starship = ref(null);
const savedRecord = ref(null);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const deleteDialogOpen = ref(false);
const error = ref("");
const validationErrors = ref({});
const form = reactive({
    name: "",
    max_atmosphering_speed: "",
    cargo_capacity: "",
});

function fillForm(data) {
    form.name = data.name;
    form.max_atmosphering_speed = ["n/a", "unknown"].includes(
        data.max_atmosphering_speed,
    )
        ? ""
        : String(data.max_atmosphering_speed).replaceAll(",", "");
    form.cargo_capacity =
        data.cargo_capacity === "unknown"
            ? ""
            : String(data.cargo_capacity).replaceAll(",", "");
}

function limitNumericInput(field, event, maxDigits) {
    form[field] = event.target.value.replace(/\D/g, "").slice(0, maxDigits);
    event.target.value = form[field];
}

function goBack() {
    if (window.history.state?.back) {
        router.back();
        return;
    }

    router.push({ name: "movies" });
}

async function loadStarship() {
    loading.value = true;
    error.value = "";

    try {
        const [swapiStarship, localStarship] = await Promise.all([
            swapiApi.getStarship(route.params.starshipId),
            starshipApi.getBySwapiId(route.params.starshipId),
        ]);

        starship.value = swapiStarship;
        savedRecord.value = localStarship;
        fillForm(localStarship ?? swapiStarship);
    } catch (requestError) {
        error.value = errorMessage(
            requestError,
            "No fue posible cargar el detalle de la nave.",
        );
    } finally {
        loading.value = false;
    }
}

async function saveStarship() {
    saving.value = true;
    error.value = "";
    validationErrors.value = {};

    const payload = {
        swapi_id: starship.value.id,
        name: form.name,
        max_atmosphering_speed: Number(form.max_atmosphering_speed),
        cargo_capacity: Number(form.cargo_capacity),
    };

    try {
        savedRecord.value = savedRecord.value
            ? await starshipApi.update(savedRecord.value.id, payload)
            : await starshipApi.create(payload);
    } catch (requestError) {
        validationErrors.value = requestError.response?.data?.errors ?? {};
        error.value = errorMessage(
            requestError,
            "No fue posible guardar la nave.",
        );
    } finally {
        saving.value = false;
    }
}

async function removeStarship() {
    if (!savedRecord.value) return;

    deleting.value = true;
    try {
        await starshipApi.remove(savedRecord.value.id);
        savedRecord.value = null;
        deleteDialogOpen.value = false;
        fillForm(starship.value);
    } catch (requestError) {
        error.value = errorMessage(
            requestError,
            "No fue posible eliminar la nave.",
        );
    } finally {
        deleting.value = false;
    }
}

onMounted(loadStarship);
</script>

<template>
    <AppBreadcrumbs
        :items="[
            { label: 'Naves', to: '/movies/1/starships' },
            { label: starship?.name ?? 'Detalle' },
        ]"
    />

    <div v-if="loading" class="panel mt-10 px-7 py-16 text-center text-muted">
        Cargando datos de la nave…
    </div>

    <div v-else-if="!starship" class="panel mt-10 px-7 py-12 text-center">
        <p class="text-danger">{{ error }}</p>
        <button class="btn-secondary mt-5" type="button" @click="loadStarship">
            Reintentar
        </button>
    </div>

    <template v-else>
        <header class="mt-9">
            <h1 class="page-title">{{ starship.name }}</h1>
            <p class="page-description">
                Consulta los datos principales, ajusta tres valores y guarda el
                registro en la API local.
            </p>
        </header>

        <p
            v-if="error"
            class="mt-6 rounded-xl border border-danger/30 bg-red-950/30 p-4 text-danger"
        >
            {{ error }}
        </p>

        <div class="mt-8 grid gap-6 xl:grid-cols-[1.55fr_1fr]">
            <form
                class="panel flex min-h-180 flex-col p-6 md:p-8"
                @submit.prevent="saveStarship"
            >
                <div>
                    <h2 class="text-2xl font-semibold">Datos de la nave</h2>
                    <p class="mt-6 text-muted">
                        Información primaria obtenida desde SWAPI.
                    </p>

                    <dl class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-xl bg-space-800 p-5">
                            <dt
                                class="text-xs font-semibold text-muted uppercase"
                            >
                                Modelo
                            </dt>
                            <dd class="mt-3 font-semibold text-gray-100">
                                {{ starship.model }}
                            </dd>
                        </div>
                        <div class="rounded-xl bg-space-800 p-5">
                            <dt
                                class="text-xs font-semibold text-muted uppercase"
                            >
                                Fabricante
                            </dt>
                            <dd class="mt-3 font-semibold text-gray-100">
                                {{ starship.manufacturer }}
                            </dd>
                        </div>
                        <div class="rounded-xl bg-space-800 p-5">
                            <dt
                                class="text-xs font-semibold text-muted uppercase"
                            >
                                Tripulación
                            </dt>
                            <dd class="mt-3 font-semibold text-gray-100">
                                {{ starship.crew }}
                            </dd>
                        </div>
                    </dl>

                    <fieldset class="mt-8 space-y-6">
                        <legend class="mb-5 text-xl font-semibold">
                            Guardar nave en la colección
                        </legend>

                        <label class="block">
                            <span class="mb-2 block font-semibold">Nombre</span>
                            <input
                                v-model.trim="form.name"
                                class="field"
                                required
                                maxlength="80"
                            />
                            <span class="mt-2 block text-xs text-muted">
                                {{ form.name.length }}/80 caracteres
                            </span>
                            <span
                                v-if="validationErrors.name"
                                class="mt-2 block text-sm text-danger"
                            >
                                {{ validationErrors.name[0] }}
                            </span>
                        </label>

                        <label class="block">
                            <span class="mb-2 block font-semibold">
                                Velocidad máxima
                            </span>
                            <input
                                :value="form.max_atmosphering_speed"
                                class="field"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                required
                                @input="
                                    limitNumericInput(
                                        'max_atmosphering_speed',
                                        $event,
                                        6,
                                    )
                                "
                            />
                            <span class="mt-2 block text-xs text-muted">
                                Máximo 6 dígitos
                            </span>
                            <span
                                v-if="validationErrors.max_atmosphering_speed"
                                class="mt-2 block text-sm text-danger"
                            >
                                {{ validationErrors.max_atmosphering_speed[0] }}
                            </span>
                        </label>

                        <label class="block">
                            <span class="mb-2 block font-semibold">
                                Capacidad de carga
                            </span>
                            <input
                                :value="form.cargo_capacity"
                                class="field"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="15"
                                required
                                @input="
                                    limitNumericInput(
                                        'cargo_capacity',
                                        $event,
                                        15,
                                    )
                                "
                            />
                            <span class="mt-2 block text-xs text-muted">
                                Máximo 15 dígitos
                            </span>
                            <span
                                v-if="validationErrors.cargo_capacity"
                                class="mt-2 block text-sm text-danger"
                            >
                                {{ validationErrors.cargo_capacity[0] }}
                            </span>
                        </label>
                    </fieldset>
                </div>

                <div
                    class="mt-auto flex flex-col-reverse justify-between gap-4 pt-10 sm:flex-row"
                >
                    <button class="btn-secondary" type="button" @click="goBack">
                        Cancelar
                    </button>
                    <button
                        class="btn-primary"
                        type="submit"
                        :disabled="saving"
                    >
                        {{
                            saving
                                ? "Guardando…"
                                : savedRecord
                                  ? "Actualizar nave"
                                  : "Agregar a naves guardadas"
                        }}
                    </button>
                </div>
            </form>

            <aside class="panel self-start p-6 md:p-8">
                <template v-if="savedRecord">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-2xl font-semibold">Nave guardada</h2>
                        <span
                            class="rounded-full bg-emerald-950 px-5 py-2 text-xs font-semibold text-success"
                        >
                            Guardada
                        </span>
                    </div>
                    <p class="mt-8 text-muted">
                        Vista previa de la nave guardada
                    </p>

                    <dl class="mt-6 space-y-8 rounded-xl bg-space-800 p-6">
                        <div class="flex justify-between gap-4">
                            <dt
                                class="text-xs font-semibold text-muted uppercase"
                            >
                                ID
                            </dt>
                            <dd>
                                #SW-{{
                                    String(savedRecord.id).padStart(4, "0")
                                }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt
                                class="text-xs font-semibold text-muted uppercase"
                            >
                                Nombre
                            </dt>
                            <dd class="text-right">{{ form.name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt
                                class="text-xs font-semibold text-muted uppercase"
                            >
                                Velocidad
                            </dt>
                            <dd>{{ form.max_atmosphering_speed || 0 }} km/h</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt
                                class="text-xs font-semibold text-muted uppercase"
                            >
                                Carga
                            </dt>
                            <dd>{{ form.cargo_capacity || 0 }} kg</dd>
                        </div>
                    </dl>

                    <div class="mt-6 rounded-xl bg-star-soft p-5">
                        <span
                            class="block text-xs font-semibold text-star uppercase"
                        >
                            API local
                        </span>
                        <code class="mt-3 block text-gray-100">
                            GET /api/starships/{{ savedRecord.id }}
                        </code>
                        <span class="mt-2 block text-sm text-muted">
                            200 · application/json
                        </span>
                    </div>

                    <button
                        class="btn-danger mt-6"
                        type="button"
                        @click="deleteDialogOpen = true"
                    >
                        Eliminar
                    </button>
                </template>

                <div v-else class="py-10 text-center">
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-full bg-star-soft text-2xl text-star"
                        aria-hidden="true"
                    >
                        +
                    </div>
                    <h2 class="mt-5 text-2xl font-semibold">
                        Esta nave aún no está guardada
                    </h2>
                    <p class="mx-auto mt-3 max-w-sm leading-7 text-muted">
                        Completa los tres campos y agrégala para verla en Naves
                        guardadas.
                    </p>
                </div>
            </aside>
        </div>
    </template>

    <ConfirmDialog
        :open="deleteDialogOpen"
        title="Eliminar nave guardada"
        :message="`Esta acción eliminará ${savedRecord?.name ?? 'esta nave'} de forma permanente.`"
        :loading="deleting"
        @cancel="deleteDialogOpen = false"
        @confirm="removeStarship"
    />
</template>
