import { usePage } from '@inertiajs/vue3';

export function useAuth() {
    const user = usePage().props.auth.user;
    return {
        user,
        hasRole: (...roles) => roles.includes(user.role),
        isAdmin: () => user.role === 'admin',
        isDoctor: () => user.role === 'doctor',
        isSecretaria: () => user.role === 'secretary',
    };
}
