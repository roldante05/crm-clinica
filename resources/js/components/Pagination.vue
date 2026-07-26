<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type PaginatedData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

defineProps<{
    paginated: PaginatedData<unknown>;
}>();
</script>

<template>
    <nav v-if="paginated.last_page > 1" class="flex items-center justify-between pt-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Mostrando {{ (paginated.current_page - 1) * paginated.per_page + 1 }}
            a {{ Math.min(paginated.current_page * paginated.per_page, paginated.total) }}
            de {{ paginated.total }} resultados
        </p>

        <div class="flex gap-1">
            <template v-for="link in paginated.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="rounded-lg border px-3 py-1.5 text-sm transition-colors"
                    :class="[
                        link.active
                            ? 'border-blue-600 bg-blue-600 text-white'
                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    preserve-scroll
                >
                    {{ link.label }}
                </Link>
                <span
                    v-else
                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-300 dark:border-gray-700 dark:text-gray-600"
                >
                    {{ link.label }}
                </span>
            </template>
        </div>
    </nav>
</template>
