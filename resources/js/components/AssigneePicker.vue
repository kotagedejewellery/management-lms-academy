<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue';
import { Input } from '@/components/ui/input';

type Assignee = { id: number; name: string; employee_id: string | null };
const value = defineModel<string>({ required: true });
const query = ref('');
const results = ref<Assignee[]>([]);
const loading = ref(false);
const error = ref('');
let timer: number | undefined;
let request: AbortController | undefined;

function search(): void {
    window.clearTimeout(timer);
    value.value = '';
    results.value = [];
    error.value = '';
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
            autocomplete="off"
            placeholder="Cari nama atau ID employee"
            @input="search"
        />
        <p class="text-xs text-muted-foreground">
            Ketik minimal 2 karakter untuk mencari employee aktif.
        </p>
        <p v-if="loading" class="text-sm text-muted-foreground">
            Mencari employee…
        </p>
        <p v-else-if="error" class="text-sm text-destructive">{{ error }}</p>
        <ul
            v-else-if="results.length"
            class="max-h-48 overflow-y-auto rounded-md border border-border p-1"
            role="listbox"
        >
            <li v-for="assignee in results" :key="assignee.id">
                <button
                    type="button"
                    class="w-full rounded-sm px-3 py-2 text-left text-sm hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    @click="select(assignee)"
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
        >
            Employee aktif tidak ditemukan.
        </p>
    </div>
</template>
