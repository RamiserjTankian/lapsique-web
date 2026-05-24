import { useEffect, useState } from 'react';

type NetworkInformation = {
    saveData?: boolean;
    effectiveType?: string;
};

export function useSaveDataConnection(): boolean {
    const [saveDataMode, setSaveDataMode] = useState(false);

    useEffect(() => {
        const connection = (navigator as Navigator & { connection?: NetworkInformation }).connection;

        if (!connection) {
            return;
        }

        const update = () => {
            const slowType = connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g';
            setSaveDataMode(Boolean(connection.saveData) || slowType);
        };

        update();
        connection.addEventListener?.('change', update);

        return () => connection.removeEventListener?.('change', update);
    }, []);

    return saveDataMode;
}
