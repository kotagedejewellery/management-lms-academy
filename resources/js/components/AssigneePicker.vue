<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import { Input } from '@/components/ui/input';

type Assignee = { id: number; name: string; employee_id: string | null };
const value = defineModel<string>({ required: true });
const props = withDefaults(defineProps<{ id?: string }>(), {
    id: 'assignee-picker',
});
const query = ref('');
const results = ref<Assignee[]>([]);
const loading = ref(false);
const error = ref('');
const activeIndex = ref(-1);
const listboxId = computed(() => `${props.id}-results`);
let timer: number | undefined;
let request: AbortController | undefined;

function search(): void {
    window.clearTimeout(timer);
    value.value = '';
    results.value = [];
    error.value = '';
    activeIndex.value = -1;
    request?.abort();
    if (query.value.trim().length < 2) return;
    timer = window.setTimeout(async () => {
        request = new AbortController();
        loading.value = true;
        try {
            const response = await fetch(
                `/assignees/search?q=${encodeURIComponent(query.value.trim())}`,
                {
                    signal: request.signal,
                    headers: { Accept: 'application/json' },
                },
            );
            if (!response.ok) throw new Error();
            results.value = (await response.json()) as Assignee[];
            activeIndex.value = results.value.length ? 0 : -1;
        } catch (exception) {
            if ((exception as DOMException).name !== 'AbortError')
                error.value = 'Pencarian employee gagal. Coba lagi.';
        } finally {
            loading.value = false;
        }
    }, 250);
}

function select(assignee: Assignee): void {
    value.value = String(assignee.id);
    query.value = `${assignee.name}${assignee.employee_id ? ` — ${assignee.employee_id}` : ''}`;
    results.value = [];
    activeIndex.value = -1;
}
function moveActive(direction: 1 | -1): void {
    if (!results.value.length) return;

    activeIndex.value =
        (activeIndex.value + direction + results.value.length) %
        results.value.length;
}
function selectActive(): void {
    const assignee = results.value[activeIndex.value];
    if (assignee) select(assignee);
}
function handleEnter(event: KeyboardEvent): void {
    if (!results.value.length) return;

    event.preventDefault();
    selectActive();
}
function clearResults(): void {
    results.value = [];
    activeIndex.value = -1;
}

onBeforeUnmount(() => {
    window.clearTimeout(timer);
    request?.abort();
});
</script>

<template>
    <div class="grid gap-2">
        <Input
            v-model="query"
            :id="id"
            autocomplete="off"
            placeholder="Cari nama atau ID employee"
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="results.length > 0"
            :aria-controls="listboxId"
            :aria-activedescendant="
                activeIndex >= 0 ? `${listboxId}-${activeIndex}` : undefined
            "
            @input="search"
            @keydown.down.prevent="moveActive(1)"
            @keydown.up.prevent="moveActive(-1)"
            @keydown.enter="handleEnter"
            @keydown.esc="clearResults"
        />
        <p class="text-xs text-muted-foreground">
            Ketik minimal 2 karakter untuk mencari employee aktif.
        </p>
        <p v-if="loading" class="text-sm text-muted-foreground" role="status">
            Mencari employee…
        </p>
        <p v-else-if="error" class="text-sm text-destructive" role="alert">
            {{ error }}
        </p>
        <ul
            v-else-if="results.length"
            :id="listboxId"
            class="max-h-48 overflow-y-auto rounded-md border border-border p-1"
            role="listbox"
        >
            <li
                v-for="(assignee, index) in results"
                :key="assignee.id"
                role="presentation"
            >
                <button
                    :id="`${listboxId}-${index}`"
                    type="button"
                    role="option"
                    :aria-selected="index === activeIndex"
                    class="min-h-10 w-full rounded-sm px-3 py-2 text-left text-sm hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="index === activeIndex ? 'bg-muted' : ''"
                    @click="select(assignee)"
                    @mouseenter="activeIndex = index"
                >
                    {{ assignee.name
                    }}<span
                        v-if="assignee.employee_id"
                        class="text-muted-foreground"
                    >
                        · {{ assignee.employee_id }}</span
                    >
                </button>
            </li>
        </ul>
        <p
            v-else-if="query.trim().length >= 2 && !loading"
            class="text-sm text-muted-foreground"
            role="status"
        >
            Employee aktif tidak ditemukan.
        </p>
    </div>
</template>
