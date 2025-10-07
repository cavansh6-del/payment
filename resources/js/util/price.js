/**
 * Format a number as a Euro price string.
 *
 * @param {number|string} amount
 * @param {Object} [options]
 * @param {boolean} [options.round=true]
 * @param {boolean} [options.withSymbol=true]
 * @returns {string}
 */
export function price(amount, { round = true, withSymbol = true } = {}) {
    const numeric = typeof amount === 'string' ? parseFloat(amount) : amount;

    if (isNaN(numeric)) return '';

    const value = round
        ? numeric.toFixed(2)
        : numeric.toString();

    return withSymbol ? `€${value}` : value;
}
