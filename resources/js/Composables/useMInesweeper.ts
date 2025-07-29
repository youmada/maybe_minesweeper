import useToastStore from '@/stores/notificationToast';
import axios from 'axios';
import { reactive, ref } from 'vue';

export function useMinesweeper() {
    // 内部定数
    const isDuplicateClick = ref(false);
    const roomConfig = reactive<{ roomId: string | null }>({
        roomId: null,
    });
    const { popUpToast } = useToastStore();
    const settingMultiPlay = (roomId: string) => {
        roomConfig.roomId = roomId;
    };

    const handleOpenAction = async (x: number, y: number) => {
        try {
            const response = await axios.put(
                `/multi/rooms/${roomConfig.roomId}/play/operate`,
                {
                    x: x,
                    y: y,
                    operation: 'open',
                },
            );
            checkResponseStatus(response);
        } catch (error: any) {
            if (error.response) {
                checkResponseStatus(error.response); // 失敗時もstatus別に分けられる
            } else {
                popUpToast('通信エラーが発生しました', 'error');
            }
        }
    };

    const handleFirstClickAction = async (x: number, y: number) => {
        try {
            const response = await axios.put(
                `/multi/rooms/${roomConfig.roomId}/play/operate`,
                {
                    x: x,
                    y: y,
                    operation: 'open',
                },
            );
            return response.data.data;
        } catch (error: any) {
            if (error.response) {
                checkResponseStatus(error.response); // 失敗時もstatus別に分けられる
            } else {
                popUpToast('通信エラーが発生しました', 'error');
            }
        }
    };

    const startGame = async (): Promise<boolean> => {
        if (isDuplicateClick.value) return false;
        isDuplicateClick.value = true;
        // 今後リスタートの際にはステータスが必要になる。
        const response = await axios.post(
            `/multi/rooms/${roomConfig.roomId}/play/start`,
        );
        isDuplicateClick.value = false;
        // 正常に処理できない場合は、ゲーム開始しない
        return response.status === 201;
    };

    const handleFlagAction = async (x: number, y: number) => {
        try {
            const response = await axios.put(
                `/multi/rooms/${roomConfig.roomId}/play/operate`,
                {
                    x: x,
                    y: y,
                    operation: 'flag',
                },
            );
            checkResponseStatus(response);
        } catch (error: any) {
            if (error.response) {
                checkResponseStatus(error.response); // 失敗時もstatus別に分けられる
            } else {
                popUpToast('通信エラーが発生しました', 'error');
            }
        }
    };

    const checkResponseStatus = (response: any) => {
        switch (response.status) {
            case 201:
                break;
            case 400:
                popUpToast('このターン操作することはできません', 'warning');
                break;
            case 403:
                popUpToast('このターン操作することはできません', 'warning');
                break;
            case 500:
                popUpToast('サーバーでエラーが発生しました', 'error');
                break;
            default:
                popUpToast('予期せぬエラーが発生しました', 'error');
                break;
        }
    };
    return {
        handleFirstClickAction,
        handleOpenAction,
        handleFlagAction,
        settingMultiPlay,
        startGame,
    };
}
