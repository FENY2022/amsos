function sendMessage() {
    const userInput = document.getElementById('userInput').value.trim();

    if (userInput === '') {
        addMessageToChatWindow('bot', 'Please enter both the equipment type and the amount (e.g., "desktop 30000").');
        return;
    }

    const [equipmentType, amountStr] = userInput.split(' ');

    if (!equipmentType || !amountStr || isNaN(amountStr)) {
        addMessageToChatWindow('bot', 'Invalid format. Please use: "equipment amount" (e.g., "laptop 50000").');
        return;
    }

    const amount = parseInt(amountStr);

    if (amount <= 0) {
        addMessageToChatWindow('bot', 'Amount should be a positive number.');
        return;
    }

    addMessageToChatWindow('user', `Equipment: ${equipmentType}, Amount: ${amount}`);

    // Generate and display the response
    const response = suggestSpecs(equipmentType.toLowerCase(), amount);
    addMessageToChatWindow('bot', response);

    // Clear the input
    document.getElementById('userInput').value = '';
}

function addMessageToChatWindow(sender, message) {
    const chatWindow = document.getElementById('chatWindow');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${sender}`;
    messageDiv.innerHTML = `<p>${message}</p>`;
    chatWindow.appendChild(messageDiv);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

function suggestSpecs(equipmentType, amount) {
    if (equipmentType === 'desktop') {
        if (amount < 15000) {
            return 'For a desktop under $15,000, consider: Intel Core i3, 8GB RAM, 500GB HDD.';
        } else if (amount < 30000) {
            return 'For a desktop under $30,000, consider: Intel Core i5, 16GB RAM, 1TB SSD.';
        } else if (amount < 60000) {
            return 'For a desktop under $60,000, consider: Intel Core i7, 32GB RAM, 2TB SSD, NVIDIA GeForce RTX 3070.';
        } else if (amount < 100000) {
            return 'For a desktop under $100,000, consider: AMD Ryzen 9, 64GB RAM, 4TB NVMe SSD, NVIDIA GeForce RTX 3090.';
        } else {
            return 'For a high-end desktop over $100,000, consider: AMD Threadripper, 128GB RAM, 8TB NVMe SSD, NVIDIA GeForce RTX 4090, custom liquid cooling.';
        }
    } else if (equipmentType === 'laptop') {
        if (amount < 15000) {
            return 'For a laptop under $15,000, consider: Intel Core i3, 8GB RAM, 256GB SSD.';
        } else if (amount < 30000) {
            return 'For a laptop under $30,000, consider: Intel Core i5, 16GB RAM, 512GB SSD.';
        } else if (amount < 60000) {
            return 'For a laptop under $60,000, consider: Intel Core i7, 32GB RAM, 1TB SSD, NVIDIA GeForce RTX 3060.';
        } else if (amount < 100000) {
            return 'For a laptop under $100,000, consider: Intel Core i9, 64GB RAM, 2TB NVMe SSD, NVIDIA GeForce RTX 3080.';
        } else {
            return 'For a high-end laptop over $100,000, consider: AMD Ryzen 9, 128GB RAM, 4TB NVMe SSD, NVIDIA GeForce RTX 4090, 4K OLED display.';
        }
    } else {
        return 'Please provide a valid equipment type (desktop or laptop).';
    }
}
