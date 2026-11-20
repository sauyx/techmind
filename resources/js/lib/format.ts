export function formatPercent(value: number): string {
    const rounded = Math.round(value * 10) / 10;
    if (Number.isInteger(rounded)) {
        return `${rounded}%`;
    }
    return `${rounded.toFixed(1).replace('.', ',')}%`;
}
