import { ref } from 'vue';

export function useToast() {
    const toastText = ref('');
    const isToastShow = ref(false);

    const showToast = (text: string) => {
        isToastShow.value = true;
        toastText.value = text;
        setTimeout(() => {
            isToastShow.value = false;
            toastText.value = '';
        }, 2000);
    };

    return {
        toastText,
        isToastShow,
        showToast,
    };
}
