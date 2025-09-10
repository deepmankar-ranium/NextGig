export const getUserDisplayName = (user) => {
    if (!user || typeof user !== 'object') return 'Unknown User';
    const name = user.full_name || user.name || user.first_name || user.username || '';
    return name.trim() || 'Unknown User';
};

export const getUserInitials = (user) => {
    if (!user || typeof user !== 'object') return '?';
    const name = getUserDisplayName(user);
    if (name === 'Unknown User') return '?';

    try {
        const words = name.trim().split(/\s+/);
        if (words.length === 1) {
            return words[0].charAt(0).toUpperCase();
        }
        return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
    } catch (error) {
        return '?';
    }
};

export const getMessageText = (message) => {
    if (!message || typeof message !== 'object') return '';
    return message.message || message.text || message.content || '';
};

export const formatTime = (timestamp) => {
    if (!timestamp) return '';
    try {
        const date = new Date(timestamp);
        if (isNaN(date.getTime())) return '';
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (error) {
        console.warn('Error formatting time:', error);
        return '';
    }
};
