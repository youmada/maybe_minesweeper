import { defineStore } from 'pinia';

interface ToastData {
    id: number;
    message: string;
    type: 'info' | 'success' | 'error' | 'warning';
}

export const useToastStore = defineStore('toastStore', {
    state: () => ({
        toasts: [] as ToastData[],
        nextId: 1, // 自動的にIDを振るため
    }),

    getters: {
        getToastData: (state) => {
            return state;
        },
    },
    actions: {
        popUpToast(message: string, type: ToastData['type'] = 'info') {
            const id = this.nextId++;
            const toast: ToastData = { id, message, type };
            this.toasts.push(toast);
            setTimeout(() => {
                this.removeToast(id);
            }, 3000);
        },
        removeToast(id: number) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
    },
});

export default useToastStore;
