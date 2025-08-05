import { onMounted, onUnmounted, Ref, ref, watch } from 'vue';

export function useElementObserver(target: Ref<HTMLElement | null>) {
    const isVisible = ref(true);

    let observer: IntersectionObserver | null = null;

    const createObserver = () => {
        if (!target.value) return;

        observer = new IntersectionObserver(
            ([entry]) => {
                isVisible.value = entry.isIntersecting;
            },
            {
                root: null, // viewport
                threshold: 0.5, // 50% 以上見えていたら true
            },
        );

        observer.observe(target.value);
    };

    const destroyObserver = () => {
        if (observer && target.value) {
            observer.unobserve(target.value);
            observer.disconnect();
            observer = null;
        }
    };

    onMounted(() => {
        watch(
            target,
            (element) => {
                if (element) {
                    createObserver();
                }
            },
            { immediate: true },
        );
    });

    onUnmounted(() => {
        destroyObserver();
    });

    return {
        isVisible,
    };
}
