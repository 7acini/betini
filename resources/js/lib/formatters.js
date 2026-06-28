export const moneyFormatter = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

export function onlyDigits(value) {
    return String(value ?? '').replace(/\D+/g, '');
}
