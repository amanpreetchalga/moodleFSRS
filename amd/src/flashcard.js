define(['core/notification'], function(Notification) {
    const selectors = {
        container: '.fsrs-container',
        showAnswer: '.fsrs-show-answer',
        cardFront: '.fsrs-card-front',
        cardBack: '.fsrs-card-back',
        reviewButton: '.fsrs-review-buttons button',
        overlay: '.fsrs-overlay',
    };

    const toggleAnswer = (container) => {
        const front = container.querySelector(selectors.cardFront);
        const back = container.querySelector(selectors.cardBack);
        if (front && back) {
            front.classList.add('hidden');
            back.classList.remove('hidden');
        }
    };

    const showOverlay = (container, days) => {
        const overlay = container.querySelector(selectors.overlay);
        if (!overlay) {
            return;
        }

        const template = overlay.dataset.template || overlay.textContent;
        const message = template.replace(/\{\{?days\}?\}/g, days);
        overlay.textContent = message;
        overlay.classList.remove('hidden');
    };

    const handleReview = (container, rate) => {
        const reviewUrl = container.dataset.reviewUrl;
        const flashcardid = container.dataset.flashcardId;
        const sesskey = container.dataset.sesskey;

        fetch(reviewUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                flashcardid: flashcardid,
                rating: rate,
                sesskey: sesskey,
            }),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then((data) => {
                const days = data.next_interval_days ?? '';
                showOverlay(container, days);
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            })
            .catch((error) => Notification.exception(error));
    };

    const registerEvents = (container) => {
        const showAnswerBtn = container.querySelector(selectors.showAnswer);
        if (showAnswerBtn) {
            showAnswerBtn.addEventListener('click', (e) => {
                e.preventDefault();
                toggleAnswer(container);
            });
        }

        container.querySelectorAll(selectors.reviewButton).forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const rate = button.dataset.rate;
                handleReview(container, rate);
            });
        });
    };

    const init = () => {
        document.querySelectorAll(selectors.container).forEach((container) => {
            registerEvents(container);
        });
    };

    return {init};
});