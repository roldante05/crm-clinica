<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { watch } from 'vue';

type ToastFlash = {
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
};

const page = usePage();

watch(
    () => page.props.toast as ToastFlash | undefined,
    (flash) => {
        if (!flash?.message) {
            return;
        }

        switch (flash.type) {
            case 'success':
                toast.success(flash.message);
                break;
            case 'error':
                toast.error(flash.message);
                break;
            case 'warning':
                toast.warning(flash.message);
                break;
            case 'info':
                toast.info(flash.message);
                break;
            default:
                toast(flash.message);
                break;
        }
    },
    { immediate: true },
);
</script>
