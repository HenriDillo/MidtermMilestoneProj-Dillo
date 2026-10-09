// app.js - the browser-side code for the site.
// Five parts:
//   0) Page chrome (no site name, highlight the current menu link)
//   1) Form validation (quick feedback, same rules as classes/Validator.php)
//   2) Add / remove ingredient boxes
//   3) Save / un-save favorites without reloading the page (fetch)
//   4) "Are you sure?" before deleting
//
// The server checks everything again, so this file is only for convenience.

'use strict';

/* =========================================================
   0) PAGE CHROME
   ========================================================= */

// The site has no name. The header logo and the footer are removed here,
// and the browser tab title keeps only the page name (the part before " | ").
document.querySelectorAll('.brand, .site-footer').forEach(function (element) {
    element.remove();
});
document.title = document.title.split(' | ')[0];

// Underline the menu link for the page you are on.
(function markCurrentPage() {
    let page = location.pathname.split('/').pop() || 'index.php';

    // A single recipe belongs under "Recipes"
    if (page === 'recipe.php') page = 'recipes.php';

    // Editing a recipe (recipe_form.php?id=5) is not "Share a Recipe"
    if (page === 'recipe_form.php' && new URLSearchParams(location.search).has('id')) page = '';

    document.querySelectorAll('.main-nav a').forEach(function (link) {
        if (link.getAttribute('href') === page) {
            link.setAttribute('aria-current', 'page');
        }
    });
})();

/* =========================================================
   1) FORM VALIDATION
   ========================================================= */

// Get the trimmed value of a form field by its name.
function val(form, name) {
    return form.elements[name].value.trim();
}

// Show the error messages. "errors" looks like { title: 'message', ... }
function showErrors(form, errors) {
    // First clear old errors
    form.querySelectorAll('.error').forEach(function (box) { box.textContent = ''; });
    form.querySelectorAll('.invalid').forEach(function (input) { input.classList.remove('invalid'); });

    // Then show the new ones
    for (const name in errors) {
        // The ingredient boxes are all named "ingredients[]"
        const selector = (name === 'ingredients') ? '[name="ingredients[]"]' : '[name="' + name + '"]';
        const input = form.querySelector(selector);
        if (!input) continue;

        input.classList.add('invalid');
        input.closest('.field').querySelector('.error').textContent = errors[name];
    }
}

function validateRegister(form) {
    const errors = {};
    const username = val(form, 'username');
    const email = val(form, 'email');
    const password = form.elements['password'].value;       // do not trim passwords
    const confirm = form.elements['confirm_password'].value;

    if (!/^[A-Za-z0-9_]{3,20}$/.test(username)) {
        errors.username = 'Username must be 3 to 20 characters: letters, numbers and underscore only.';
    }
    if (email === '' || email.length > 100 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.email = 'Please enter a valid email address.';
    }
    if (password.length < 8 || password.length > 72 || !/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) {
        errors.password = 'Password must be 8 to 72 characters with at least one letter and one number.';
    }
    if (confirm !== password) {
        errors.confirm_password = 'Passwords do not match.';
    }
    return errors;
}

function validateLogin(form) {
    const errors = {};
    if (val(form, 'login') === '') {
        errors.login = 'Please enter your username or email.';
    }
    if (form.elements['password'].value === '') {
        errors.password = 'Please enter your password.';
    }
    return errors;
}

function validateRecipe(form) {
    const errors = {};
    const title = val(form, 'title');
    const description = val(form, 'description');
    const category = val(form, 'category_id');
    const cookTime = val(form, 'cook_time_minutes');
    const servings = val(form, 'servings');
    const steps = val(form, 'steps');

    // Collect the ingredient boxes that are not empty
    const ingredients = [];
    form.querySelectorAll('[name="ingredients[]"]').forEach(function (box) {
        if (box.value.trim() !== '') ingredients.push(box.value.trim());
    });

    if (title.length < 3 || title.length > 100) {
        errors.title = 'Title must be 3 to 100 characters.';
    }
    if (description.length < 10 || description.length > 300) {
        errors.description = 'Description must be 10 to 300 characters.';
    }
    if (category === '') {
        errors.category_id = 'Please choose a category.';
    }
    if (!/^[0-9]+$/.test(cookTime) || Number(cookTime) < 1 || Number(cookTime) > 1440) {
        errors.cook_time_minutes = 'Cooking time must be a whole number from 1 to 1440 minutes.';
    }
    if (!/^[0-9]+$/.test(servings) || Number(servings) < 1 || Number(servings) > 100) {
        errors.servings = 'Servings must be a whole number from 1 to 100.';
    }
    if (ingredients.length === 0) {
        errors.ingredients = 'Add at least one ingredient.';
    } else if (ingredients.length > 30) {
        errors.ingredients = 'You can add up to 30 ingredients.';
    } else if (ingredients.some(function (item) { return item.length > 100; })) {
        errors.ingredients = 'Each ingredient must be 100 characters or less.';
    }
    if (steps.length < 20 || steps.length > 5000) {
        errors.steps = 'Cooking steps must be 20 to 5000 characters.';
    }
    return errors;
}

function validateComment(form) {
    const errors = {};
    const body = val(form, 'body');
    if (body.length < 2 || body.length > 500) {
        errors.body = 'Comment must be 2 to 500 characters.';
    }
    return errors;
}

// Which function checks which form (matches data-validate="..." in the HTML)
const validators = {
    register: validateRegister,
    login: validateLogin,
    recipe: validateRecipe,
    comment: validateComment
};

document.querySelectorAll('form[data-validate]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        const errors = validators[form.dataset.validate](form);
        showErrors(form, errors);

        if (Object.keys(errors).length > 0) {
            event.preventDefault();                       // do not send the form
            const firstBad = form.querySelector('.invalid');
            if (firstBad) firstBad.focus();
        }
    });
});

/* =========================================================
   2) ADD / REMOVE INGREDIENT BOXES
   ========================================================= */

const ingredientList = document.getElementById('ingredient-list');
const addIngredientButton = document.getElementById('add-ingredient');
const MAX_INGREDIENTS = 30;

if (ingredientList && addIngredientButton) {

    addIngredientButton.addEventListener('click', function () {
        if (ingredientList.children.length >= MAX_INGREDIENTS) {
            alert('You can add up to ' + MAX_INGREDIENTS + ' ingredients.');
            return;
        }

        const row = document.createElement('div');
        row.className = 'ingredient-row';
        row.innerHTML =
            '<input type="text" name="ingredients[]" maxlength="100" placeholder="e.g. 2 cloves garlic">' +
            '<button type="button" class="btn-remove" title="Remove" aria-label="Remove ingredient">&#10005;</button>';

        ingredientList.appendChild(row);
        row.querySelector('input').focus();
    });

    // One listener on the whole list handles every remove button (even new ones)
    ingredientList.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.btn-remove');
        if (!removeButton) return;

        const row = removeButton.parentElement;
        if (ingredientList.children.length > 1) {
            row.remove();
        } else {
            row.querySelector('input').value = '';   // keep the last box, just empty it
        }
    });
}

/* =========================================================
   3) FAVORITES (no page reload)
   ========================================================= */

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Change the button to look "saved" or "not saved"
function updateFavoriteButton(button, isSaved) {
    button.classList.toggle('is-saved', isSaved);
    button.textContent = isSaved ? '♥ Saved' : '♡ Save';
}

document.addEventListener('click', async function (event) {
    const button = event.target.closest('.btn-fav');
    if (!button) return;

    button.disabled = true;   // stop double clicks while we wait for the server

    try {
        // Send the recipe id to the server in the background
        const formData = new FormData();
        formData.append('recipe_id', button.dataset.recipeId);
        formData.append('csrf_token', csrfToken);

        const response = await fetch('ajax/toggle_favorite.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        updateFavoriteButton(button, result.favorited);

        // On the Favorites page, an un-saved recipe disappears from the list
        if (!result.favorited && button.dataset.removeCard) {
            button.closest('.card').remove();

            if (document.querySelectorAll('.card').length === 0) {
                document.getElementById('empty-favorites').hidden = false;
            }
        }
    } catch (error) {
        alert(error.message || 'Something went wrong. Please try again.');
    }

    button.disabled = false;
});

/* =========================================================
   4) CONFIRM BEFORE DELETING
   Any form with data-confirm="..." asks first.
   ========================================================= */

document.addEventListener('submit', function (event) {
    const message = event.target.dataset.confirm;
    if (message && !confirm(message)) {
        event.preventDefault();
    }
});
