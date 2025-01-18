let currentQuestionIndex = 0;
let timerInterval;
let overtime = 0;
let hintUsed = 0;
let hintUsedForCurrentQuestion = false;
let timeTaken = 0;
let lives = initialLives;
let points = 5;
let isSubmitting = false; // Verrou pour empêcher les appels multiples
let remainingTime = 0; // Temps restant pour la question actuelle
let wrongAnswers = 0; // Nombre de mauvaises réponses pour la question actuelle
let streak = 0; // Variable pour suivre la streak de bonnes réponses

function showQuestion(index) {
    const questionContainer = document.getElementById('question-container');
    const questionData = questions[index];

    if (questionData) {
        questionContainer.innerHTML = `
            <p>${questionData.texte_question}</p>
            <a href="${questionData.img}" target="_blank">
                <img src="${questionData.img}" alt="Image pour la question">
            </a>
        `;
    } else {
        questionContainer.innerHTML = `
            <p>Question non trouvée.</p>
        `;
    }

    updateProgressBar();
    updateQuestionCounter();
    startTimer(remainingTime > 0 ? remainingTime : questionData.temps);
    saveCurrentQuestionIndex(index, remainingTime); // Enregistrer l'index de la question actuelle et le temps restant
}

function updateLives() {
    for (let i = 1; i <= 4; i++) {
        const heart = document.getElementById(`heart-${i}`);
        if (i <= lives) {
            heart.style.opacity = 1;
        } else {
            heart.style.opacity = 0.3;
        }
    }
}

function nextQuestion(timeUp = false) {
    console.log('nextQuestion function called');
    if (isSubmitting) return; // Empêche les appels multiples
    isSubmitting = true; // Active le verrou

    const questionId = questions[currentQuestionIndex].id_enigmes;
    const pointsToSend = timeUp ? 0 : points;

    console.log('Submitting next question data:', {
        questionId,
        hintUsed,
        timeTaken,
        lives,
        pointsToSend,
        wrongAnswers // Inclure le nombre de mauvaises réponses
    });

    fetch('../pages/submit_answer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `question_id=${questionId}&answer=&hint_used=${hintUsed}&time_taken=${timeTaken}&lives=${lives}&points=${pointsToSend}&mauvaise_reponse=${wrongAnswers}`
    })
    .then(response => response.text()) // Change to .text() to log the raw response
    .then(data => {
        console.log('Server response:', data); // Log the raw response
        try {
            const jsonData = JSON.parse(data); // Parse the JSON data
            isSubmitting = false; // Désactive le verrou après la réponse
            if (jsonData.error) {
                console.error('Erreur:', jsonData.error, 'Détails:', jsonData.details);
            } else if (jsonData.test_completed) {
                // Afficher le popup de fin de test
                showCompletionPopup();
            } else {
                streak = 0; // Reset streak when passing the question
                updateStreak(); // Update the streak display
                if (currentQuestionIndex < questions.length - 1) {
                    currentQuestionIndex++;
                    console.log('Next question time:', questions[currentQuestionIndex].temps); // Log the time for the next question
                    hintUsed = 0; // Reset hint usage for the next question
                    hintUsedForCurrentQuestion = false; // Reset hint usage tracking for the next question
                    lives = jsonData.lives !== undefined ? jsonData.lives : 4; // Reset lives for the next question
                    points = 5; // Reset points for the next question
                    remainingTime = questions[currentQuestionIndex].temps; // Set remaining time for the next question
                    wrongAnswers = 0; // Reset wrong answers for the next question
                    document.getElementById('answer').value = ''; // Vider l'input
                    document.getElementById('error-message').textContent = ''; // Effacer le message d'erreur
                    showQuestion(currentQuestionIndex);
                    updateLives(); // Mettre à jour les cœurs
                    saveCurrentQuestionIndex(currentQuestionIndex, remainingTime); // Enregistrer l'index de la question actuelle et le temps restant
                } else {
                    // Afficher le popup de fin de test
                    showCompletionPopup();
                }
            }
        } catch (error) {
            console.error('Error parsing JSON:', error);
        }
    })
    .catch(error => {
        isSubmitting = false; // Désactive le verrou en cas d'erreur
        console.error('Error:', error);
    });
}

// Appeler updateLives au chargement de la page
updateLives();

function submitAnswer() {
    console.log('submitAnswer function called');
    if (isSubmitting) return; // Empêche les appels multiples
    isSubmitting = true; // Active le verrou

    const answer = document.getElementById('answer').value.trim(); // Utilisez .trim() pour supprimer les espaces blancs
    const questionId = questions[currentQuestionIndex].id_enigmes;

    console.log('Submitting answer:', {
        questionId,
        answer,
        hintUsed,
        timeTaken,
        lives,
        points,
        wrongAnswers // Inclure le nombre de mauvaises réponses
    });

    fetch('../pages/submit_answer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `question_id=${questionId}&answer=${encodeURIComponent(answer)}&hint_used=${hintUsed}&time_taken=${timeTaken}&lives=${lives}&points=${points}&mauvaise_reponse=${wrongAnswers}`
    })
    .then(response => response.text()) // Change to .text() to log the raw response
    .then(data => {
        console.log('Server response:', data); // Log the raw response
        try {
            const jsonData = JSON.parse(data); // Parse the JSON data
            isSubmitting = false; // Désactive le verrou après la réponse
            document.getElementById('answer').value = ''; // Vider l'input après l'envoi de la réponse
            if (jsonData.correct) {
                console.log('Bonne réponse');
                showConfettiGif(); // Afficher le GIF de confetti
                streak = jsonData.streak; // Update streak from server response
                updateStreak(); // Update the streak display
                console.log('Next question time:', questions[currentQuestionIndex + 1].temps); // Log the time for the next question
                document.getElementById('error-message').textContent = ''; // Effacer le message d'erreur
                // Appeler nextQuestion ici pour passer à la question suivante après une bonne réponse
                if (currentQuestionIndex < questions.length - 1) {
                    currentQuestionIndex++;
                    hintUsed = 0; // Reset hint usage for the next question
                    hintUsedForCurrentQuestion = false; // Reset hint usage tracking for the next question
                    lives = 4; // Reset lives for the next question
                    points = 5; // Reset points for the next question
                    remainingTime = questions[currentQuestionIndex].temps; // Set remaining time for the next question
                    wrongAnswers = 0; // Reset wrong answers for the next question
                    showQuestion(currentQuestionIndex);
                    updateLives(); // Mettre à jour les cœurs
                    saveCurrentQuestionIndex(currentQuestionIndex, remainingTime); // Enregistrer l'index de la question actuelle et le temps restant
                } else {
                    // Afficher le popup de fin de test
                    showCompletionPopup();
                }
            } else if (jsonData.error) {
                console.error('Erreur:', jsonData.error, 'Détails:', jsonData.details);
            } else {
                streak = 0; // Reset streak for incorrect answer
                updateStreak(); // Update the streak display
                lives--;
                points--;
                wrongAnswers++; // Increment wrong answers
                updateLives(); // Mettre à jour les cœurs
                saveLives(); // Sauvegarder les vies dans la base de données
                if (lives <= 0) {
                    console.log('Toutes les vies utilisées, question suivante');
                    points = 0;
                    nextQuestion();
                } else {
                    console.log('Mauvaise réponse, réessayez');
                    document.getElementById('error-message').textContent = 'Mauvaise réponse, réessayez'; // Afficher un message lorsque la réponse est incorrecte
                }
            }
        } catch (error) {
            console.error('Error parsing JSON:', error);
        }
    })
    .catch(error => {
        isSubmitting = false; // Désactive le verrou en cas d'erreur
        console.error('Error:', error);
    });
}

function loadCurrentQuestionIndex() {
    return fetch('../pages/load_question_index.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                remainingTime = data.temps_restant;
                console.log('Loaded remaining time:', remainingTime); // Log the loaded remaining time
                return data.question_en_cours;
            } else {
                console.error('Erreur lors du chargement de l\'index de la question actuelle et du temps restant:', data.error);
                return 0;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            return 0;
        });
}

document.addEventListener('DOMContentLoaded', async () => {
    currentQuestionIndex = await loadCurrentQuestionIndex(); // Charger l'index de la question actuelle
    streak = await loadCurrentStreak(); // Charger la streak actuelle
    showQuestion(currentQuestionIndex);
    updateStreak(); // Mettre à jour l'affichage de la streak

    document.getElementById('submit-button').addEventListener('click', function(event) {
        event.preventDefault();
        submitAnswer();
    });

    document.getElementById('next-button').addEventListener('click', function(event) {
        event.preventDefault();
        showConfirmationPopup();
    });

    document.getElementById('answer').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            submitAnswer();
        }
    });
});

document.getElementById('next-button').addEventListener('click', function(event) {
    event.preventDefault();
    showConfirmationPopup(); // Afficher le popup de confirmation
});

function showConfirmationPopup() {
    const confirmationPopup = document.getElementById('confirmation-popup');
    confirmationPopup.style.display = 'block';
}

function confirmNextQuestion() {
    closeConfirmationPopup();
    nextQuestion(); // Appeler nextQuestion après confirmation
}

function closeConfirmationPopup() {
    const confirmationPopup = document.getElementById('confirmation-popup');
    confirmationPopup.style.display = 'none';
}

async function loadCurrentStreak() {
    try {
        const response = await fetch('../pages/load_streak.php');
        const data = await response.json();
        if (data.success) {
            return data.streak;
        } else {
            console.error('Erreur lors du chargement de la streak:', data.error);
            return 0;
        }
    } catch (error) {
        console.error('Error:', error);
        return 0;
    }
}

window.addEventListener('beforeunload', function() {
    saveCurrentQuestionIndex(currentQuestionIndex, remainingTime);
});

function saveCurrentQuestionIndex(index, remainingTime, callback) {
    const formattedTime = formatTimeForDatabase(remainingTime);
    console.log('Saving current question index and remaining time:', index, formattedTime); // Log the values being saved
    fetch('../pages/save_question_index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `question_en_cours=${index}&temps_restant=${formattedTime}`
    })
    .then(response => {
        console.log('Response status:', response.status); // Log the response status
        return response.text(); // Change to .text() to log the raw response
    })
    .then(data => {
        console.log('Server response:', data); // Log the raw response
        try {
            const jsonData = JSON.parse(data); // Parse the JSON data
            if (jsonData.success) {
                console.log('Index de la question actuelle et temps restant enregistrés avec succès');
                if (callback) callback();
            } else {
                console.error('Erreur lors de l\'enregistrement de l\'index de la question actuelle et du temps restant:', jsonData.error);
                if (callback) callback();
            }
        } catch (error) {
            console.error('Error parsing JSON:', error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        console.error('Failed to fetch:', error.message); // Log the error message
        if (callback) callback();
    });
}

function showHint() {
    if (!hintUsedForCurrentQuestion) {
        hintUsedForCurrentQuestion = true; // Marquer l'indice comme utilisé pour la question actuelle
        hintUsed = 1; // Marquer l'indice comme utilisé
        points--; // Déduire 1 point pour l'utilisation de l'indice
    }

    const hintPopup = document.getElementById('hint-popup');
    const hintText = document.getElementById('hint-text');
    const progressContainer = document.querySelector('.progress-container');
    const testContainer = document.querySelector('.test-container');
    
    hintText.textContent = questions[currentQuestionIndex].indice;
    
    hintPopup.style.display = 'block';
    progressContainer.classList.add('blur');
    testContainer.classList.add('blur');
}

function closeHintPopup() {
    const hintPopup = document.getElementById('hint-popup');
    const progressContainer = document.querySelector('.progress-container');
    const testContainer = document.querySelector('.test-container');
    
    hintPopup.style.display = 'none';
    progressContainer.classList.remove('blur');
    testContainer.classList.remove('blur');
}

function updateProgressBar() {
    const progress = document.getElementById('progress');
    const progressPercentage = ((currentQuestionIndex + 1) / questions.length) * 100;
    progress.style.width = `${progressPercentage}%`;
}

function updateQuestionCounter() {
    const questionNumber = document.getElementById('question-number');
    questionNumber.textContent = currentQuestionIndex + 1;
}

function startTimer(timeLeft) {
    const timerElement = document.getElementById('timer');
    timeTaken = 0;

    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            overtime++;
            timerElement.textContent = `+${formatTime(overtime)}`;
            nextQuestion(true); // Passer à la question suivante avec 0 points
        } else {
            timerElement.textContent = formatTime(timeLeft);
            timeLeft--;
        }
        timeTaken++;
        remainingTime = timeLeft; // Mettre à jour le temps restant
        console.log('Remaining time:', remainingTime); // Log the remaining time
    }, 1000);
}

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
}

function formatTimeForDatabase(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
}

function showInfoPopup() {
    const infoPopup = document.getElementById('rules-popup');
    infoPopup.style.display = 'block';
}

function closeInfoPopup() {
    const infoPopup = document.getElementById('rules-popup');
    infoPopup.style.display = 'none';
}

function goToHome() {
    saveCurrentQuestionIndex(currentQuestionIndex, remainingTime, () => {
        window.location.href = '../index.php';
    });
}

function updateStreak() {
    const streakContainer = document.getElementById('streak-container');
    streakContainer.innerHTML = ''; // Clear the current streak display

    console.log('Updating streak:', streak); // Log the current streak

    if (streak > 0) {
        const streakMessage = document.createElement('p');
        streakMessage.textContent = `${streak} `;
        streakMessage.classList.add('streak-message'); // Add the CSS class for styling
        
        const fireImage = document.createElement('img');
        fireImage.src = '../public/img/fire-flame.gif';
        fireImage.alt = 'Fire';
        fireImage.classList.add('fire');
        
        streakMessage.appendChild(fireImage);
        streakContainer.appendChild(streakMessage);
    }
}

function saveLives() {
    fetch('../pages/save_lives.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `lives=${lives}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Lives saved successfully');
        } else {
            console.error('Erreur lors de la sauvegarde des vies:', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function showCompletionPopup() {
    const completionPopup = document.getElementById('completion-popup');
    completionPopup.style.display = 'block';
}

function redirectToHome() {
    fetch('../pages/mark_test_completed.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '../index.php';
        } else {
            console.error('Erreur lors de la mise à jour du champ test_completed:', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function showConfettiGif() {
    const confettiGif = document.createElement('img');
    confettiGif.src = '../public/img/output-onlinegiftools.gif';
    confettiGif.alt = 'Confetti';
    confettiGif.classList.add('confetti-gif'); // Ajoutez une classe CSS pour le style
    document.body.appendChild(confettiGif);

    // Supprimer le GIF après 3 secondes
    setTimeout(() => {
        confettiGif.remove();
    }, 2000);
}