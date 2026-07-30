<script setup>
import { nextTick, ref, watch } from "vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, required: true },
    message: { type: String, required: true },
    confirmLabel: { type: String, default: "Eliminar" },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(["cancel", "confirm"]);
const cancelButton = ref(null);

watch(
    () => props.open,
    async (open) => {
        if (!open) return;
        await nextTick();
        cancelButton.value?.focus();
    },
);

function cancel() {
    if (!props.loading) emit("cancel");
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-100 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-5 backdrop-blur-sm"
                @click.self="cancel"
                @keydown.esc="cancel"
            >
                <section
                    class="panel w-full max-w-md p-7 shadow-2xl shadow-black/50"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="confirm-dialog-title"
                    aria-describedby="confirm-dialog-description"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-full bg-red-950 text-xl text-danger"
                    >
                        !
                    </div>
                    <h2
                        id="confirm-dialog-title"
                        class="mt-5 text-2xl font-semibold text-white"
                    >
                        {{ title }}
                    </h2>
                    <p
                        id="confirm-dialog-description"
                        class="mt-3 leading-7 text-muted"
                    >
                        {{ message }}
                    </p>
                    <div
                        class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <button
                            ref="cancelButton"
                            class="btn-secondary"
                            type="button"
                            :disabled="loading"
                            @click="cancel"
                        >
                            Cancelar
                        </button>
                        <button
                            class="btn-danger"
                            type="button"
                            :disabled="loading"
                            @click="$emit('confirm')"
                        >
                            {{ loading ? "Eliminando…" : confirmLabel }}
                        </button>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>
