const registerForm = document.getElementById('registerForm');
const registerMessage = document.getElementById('registerMessage');
const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');
const contactsList = document.getElementById('contactsList');
const contactCount = document.getElementById('contactCount');
const managerStatus = document.getElementById('managerStatus');
const deleteContactsButton = document.getElementById('deleteContactsButton');
const addContactButton = document.getElementById('addContactButton');
const editContactsButton = document.getElementById('editContactsButton');
const contactSearch = document.getElementById('contactSearch');
const logoutButton = document.getElementById('logoutButton');
const cancelDeleteButton = document.getElementById('cancelDeleteButton');
const logoButton = document.getElementById('logoButton');

if (logoutButton) {
  logoutButton.addEventListener('click', function () {
    localStorage.removeItem('user');
    window.location.href = 'index.html';
  });
}

if (logoButton) {
  logoButton.addEventListener('click', function () {
    window.location.href = 'index.html';
  });
}

if (document.body) {
  document.body.classList.add('page-ready');
}

document.addEventListener('DOMContentLoaded', function () {
  const openLoginButton = document.getElementById('openLoginPanelButton');
  const closeLoginButton = document.getElementById('closeLoginPanelButton');

  if (openLoginButton) {
    openLoginButton.addEventListener('click', function () {
      document.body.classList.add('home-login-active');
    });
  }

  if (closeLoginButton) {
    closeLoginButton.addEventListener('click', function () {
      document.body.classList.remove('home-login-active');
    });
  }

  const transitionLink = document.querySelector('a[href="login.html"]');
  if (transitionLink) {
    transitionLink.addEventListener('click', function (event) {
      event.preventDefault();
      document.body.classList.remove('page-ready');
      document.body.classList.add('page-exit');
      setTimeout(() => {
        window.location.href = this.href;
      }, 220);
    });
  }
});

let deleteMode = false;
let selectedContactIds = new Set();
let isAddMode = false;
let isEditMode = false;

function setDeleteMode(active) {
  deleteMode = active;
  editContactsButton.hidden = active;
  addContactButton.hidden = active;
  cancelDeleteButton.hidden = !active;
}

const ENABLE_MOCK_CONTACT_PREVIEW = false; // Set to true to enable mock contact preview mode
const MOCK_CONTACTS = [
  {
    id: 1,
    firstName: 'Alicia',
    lastName: 'Fernandez',
    phone: '(407) 555-0192',
    email: 'alicia.fernandez@example.com'
  },
  {
    id: 2,
    firstName: 'Marcus',
    lastName: 'Lee',
    phone: '(305) 555-0146',
    email: 'marcus.lee@example.com'
  },
  {
    id: 3,
    firstName: 'Priya',
    lastName: 'Shah',
    phone: '(786) 555-0184',
    email: 'priya.shah@example.com'
  },
  {
    id: 4,
    firstName: 'Daniel',
    lastName: 'Nguyen',
    phone: '(561) 555-0177',
    email: 'daniel.nguyen@example.com'
  }
];

async function parseJsonResponse(response) {
  const text = await response.text();

  if (!text) {
    return {};
  }

  try {
    return JSON.parse(text);
  } catch (error) {
    throw new Error(
      `The server did not return JSON. Check that PHP is running and the page is being served over http://, not opened as a file.`
    );
  }
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, function (char) {
    const entities = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    };
    return entities[char] || char;
  });
}

function updateContactCount(count) {
  if (contactCount) {
    contactCount.textContent = count;
  }
}

function renderContactsTable(contacts) {
  updateContactCount(contacts.length);

  if (!contacts.length && !isAddMode) {
    contactsList.innerHTML = '<p>No contacts found.</p>';
    return;
  }

  //adding contacts
  const addRow = isAddMode ? `
    <tr class="add-row">
      <td class="select-cell"></td> 
      <td class="name-fields">
        <input type="text" id="newFirstName" placeholder="First Name">
        <input type="text" id="newLastName" placeholder="Last Name">
      </td>
      <td><input type="text" id="newPhone" placeholder="Phone"></td>
      <td>
        <div class="add-contact-controls">
          <input type="email" id="newEmail" placeholder="Email">
          <button type="button" id="saveNewContactButton" class="save-contact-button">Save</button>
          <button type="button" id="cancelContactButton" class="cancel-contact-button">Cancel</button>

        </div>
      </td>
    </tr>
  ` : '';

  contactsList.innerHTML = `
    <table class="contact-table">
      <thead>
        <tr>
          <th class="select-cell">${deleteMode ? 'Select' : ''}</th>
          ${isEditMode ? `
            <th>Name</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Action</th>
          ` : `
            <th>Name</th>
            <th>Phone Number</th>
            <th>Email</th>
          `}
        </tr>
      </thead>
      <tbody>
        ${addRow}
        ${contacts.map(contact => {
          if (isEditMode) {
            return `
              <tr class="edit-row" data-id="${contact.id}">
                <td class="select-cell"></td>
                <td class="name-fields">
                  <input type="text" data-field="firstName" placeholder="First Name" value="${escapeHtml(contact.firstName || '')}">
                  <input type="text" data-field="lastName" placeholder="Last Name" value="${escapeHtml(contact.lastName || '')}">
                </td>
                <td><input type="text" data-field="phone" value="${escapeHtml(contact.phone || '')}"></td>
                <td><input type="email" data-field="email" value="${escapeHtml(contact.email || '')}"></td>
                <td>
                  <div class="edit-contact-controls">
                    <button type="button" class="save-edit-button" data-id="${contact.id}">Save</button>
                    <button type="button" class="cancel-edit-button" data-id="${contact.id}">Cancel</button>
                  </div>
                </td>
              </tr>
            `;
          }

          return `
            <tr class="${selectedContactIds.has(contact.id) ? 'contact-row-selected' : ''}">
              <td class="select-cell">
                ${deleteMode ? `<input type="checkbox" class="contact-select" data-id="${contact.id}" ${selectedContactIds.has(contact.id) ? 'checked' : ''}>` : ''}
              </td>
              <td>${escapeHtml(contact.firstName || '')} ${escapeHtml(contact.lastName || '')}</td>
              <td>${escapeHtml(contact.phone || 'N/A')}</td>
              <td>${escapeHtml(contact.email || 'N/A')}</td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
  `;
}

async function loadContacts(searchTerm = '') {
  if (!contactsList) {
    return;
  }

  if (ENABLE_MOCK_CONTACT_PREVIEW) {
    const query = searchTerm.trim().toLowerCase();
    const contacts = MOCK_CONTACTS.filter(contact => {
      if (!query) {
        return true;
      }

      const fullName = `${contact.firstName || ''} ${contact.lastName || ''}`.toLowerCase();
      const phone = (contact.phone || '').toLowerCase();
      const email = (contact.email || '').toLowerCase();

      return fullName.includes(query) || phone.includes(query) || email.includes(query);
    });

    if (managerStatus) {
      managerStatus.textContent = 'Preview mode: showing mock contact data.';
      managerStatus.classList.remove('text-danger');
    }

    renderContactsTable(contacts);
    return;
  }

  const storedUser = localStorage.getItem('user');
  if (!storedUser) {
    updateContactCount(0);
    contactsList.innerHTML = '<p>Please log in to view your contacts.</p>';
    return;
  }

  let user;
  try {
    user = JSON.parse(storedUser);
  } catch (error) {
    updateContactCount(0);
    contactsList.innerHTML = '<p>Your session is invalid. Please log in again.</p>';
    return;
  }

  if (!user.id) {
    updateContactCount(0);
    contactsList.innerHTML = '<p>No user session found.</p>';
    return;
  }

  try {
    const response = await fetch('LAMPAPI/SearchData.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        UserID: user.id,
        Search: searchTerm.trim(),
        Page: 1,
        ResultsPerPage: 50
      })
    });

    const result = await parseJsonResponse(response);

    if (!response.ok || result.error) {
      throw new Error(result.error || 'Failed to load contacts.');
    }

    const contacts = result.results || [];

    if (!contacts.length && !isAddMode) {
      updateContactCount(0);
      contactsList.innerHTML = '<p>No contacts found.</p>';
      return;
    }

    renderContactsTable(contacts);
  } catch (error) {
    updateContactCount(0);
    if (managerStatus) {
      managerStatus.textContent = error.message;
      managerStatus.classList.add('text-danger');
    }
    contactsList.innerHTML = '<p>Unable to load contacts.</p>';
  }
}

if (registerForm) {
  registerForm.addEventListener('submit', async function (event) {
    event.preventDefault();

    const firstNameInput = document.getElementById('registerFirstName');
    const lastNameInput = document.getElementById('registerLastName');
    const loginInput = document.getElementById('registerLogin');
    const passwordInput = document.getElementById('registerPassword');

    if (!firstNameInput || !lastNameInput || !loginInput || !passwordInput) {
      if (registerMessage) {
        registerMessage.textContent = 'Registration form is unavailable. Please refresh the page.';
        registerMessage.classList.add('text-danger');
      }
      return;
    }

    const formData = {
      FirstName: firstNameInput.value.trim(),
      LastName: lastNameInput.value.trim(),
      Login: loginInput.value.trim(),
      Password: passwordInput.value
    };

    registerMessage.textContent = '';
    registerMessage.className = 'mt-2';

    try {
      const response = await fetch('LAMPAPI/Register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const result = await parseJsonResponse(response);

      if (!response.ok || result.error) {
        throw new Error(result.error || 'Registration failed.');
      }

      registerMessage.textContent = 'Registration successful! You can now log in.';
      registerMessage.classList.add('text-success');
      registerForm.reset();

      setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
        if (modal) {
          modal.hide();
        }
      }, 1500);
    } catch (error) {
      registerMessage.textContent = error.message;
      registerMessage.classList.add('text-danger');
    }
  });
}

if (loginForm) {
  loginForm.addEventListener('submit', async function (event) {
    event.preventDefault();

    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value;

    if (loginMessage) {
      loginMessage.textContent = '';
      loginMessage.className = 'mt-2';
    }

    if (!login || !password) {
      if (loginMessage) {
        loginMessage.textContent = 'Username and password are required.';
        loginMessage.classList.add('text-danger');
      }
      return;
    }

    try {
      const response = await fetch('LAMPAPI/Login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          Login: login,
          Password: password
        })
      });

      const result = await parseJsonResponse(response);

      if (!response.ok || result.error) {
        throw new Error(result.error || 'Invalid username or password.');
      }

      localStorage.setItem('user', JSON.stringify({
        id: result.id,
        firstName: result.firstName,
        lastName: result.lastName
      }));

      window.location.href = 'manager.html';
    } catch (error) {
      if (loginMessage) {
        loginMessage.textContent = error.message;
        loginMessage.classList.add('text-danger');
      }
    }
  });
}

if (deleteContactsButton) {

  deleteContactsButton.addEventListener('click', async function () {
    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
      alert('Please log in to delete contacts.');
      return;
    }

    if (!deleteMode) {
      setDeleteMode(true);
      deleteContactsButton.textContent = 'Confirm Delete';
      await loadContacts();
      return;
    }

    const ids = Array.from(document.querySelectorAll('.contact-select:checked')).map(input => Number(input.dataset.id));

    if (!ids.length) {
      alert('Select at least one contact to delete.');
      return;
    }

    const confirmed = window.confirm(`Delete ${ids.length} selected contact(s)?`);
    if (!confirmed) {
      return;
    }

    let user;
    try {
      user = JSON.parse(storedUser);
    } catch (error) {
      alert('Your session is invalid. Please log in again.');
      return;
    }

    try {
      const response = await fetch('LAMPAPI/DeleteData.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          UserID: user.id,
          ids: ids
        })
      });

      const result = await parseJsonResponse(response);

      if (!response.ok || result.error) {
        throw new Error(result.error || 'Failed to delete contacts.');
      }

      selectedContactIds.clear();
      setDeleteMode(false);
      deleteContactsButton.textContent = 'Delete Contacts';
      await loadContacts();
      alert('Selected contact(s) deleted successfully.');
    } catch (error) {
      if (managerStatus) {
        managerStatus.textContent = error.message;
        managerStatus.classList.add('text-danger');
      }
      alert(error.message);
    }
  });
}

if (cancelDeleteButton) {
  cancelDeleteButton.addEventListener('click', async function () {
    setDeleteMode(false);
    selectedContactIds.clear();
    deleteContactsButton.textContent = 'Delete Contacts';
    await loadContacts();
  });
}

async function saveNewContact() {
  const storedUser = localStorage.getItem('user');
  if (!storedUser) {
    alert('Please log in to add a contact.');
    return;
  }

  let user;
  try {
    user = JSON.parse(storedUser);
  } catch (error) {
    alert('Your session is invalid. Please log in again.');
    return;
  }

  const firstName = document.getElementById('newFirstName')?.value.trim();
  const lastName = document.getElementById('newLastName')?.value.trim();
  const phone = document.getElementById('newPhone')?.value.trim();
  const email = document.getElementById('newEmail')?.value.trim();

  if (!firstName || !lastName) {
    alert('First name and last name are required.');
    return;
  }

  try {
    const response = await fetch('LAMPAPI/AddData.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        UserID: user.id,
        FirstName: firstName,
        LastName: lastName,
        Phone: phone,
        Email: email,
        CreationDate: new Date().toISOString().slice(0, 19).replace('T', ' ')
      })
    });

    const result = await parseJsonResponse(response);

    if (!response.ok || result.error) {
      throw new Error(result.error || 'Failed to add contact.');
    }

    isAddMode = false;
    await loadContacts();
    alert('Contact added successfully.');
  } catch (error) {
    if (managerStatus) {
      managerStatus.textContent = error.message;
      managerStatus.classList.add('text-danger');
    }
    alert(error.message);
  }
}

if (editContactsButton) {
  editContactsButton.addEventListener('click', async function () {
    if (deleteMode) {
      setDeleteMode(false);
      deleteContactsButton.textContent = 'Delete Contacts';
      selectedContactIds.clear();
    }

    if (isAddMode) {
      isAddMode = false;
    }

    isEditMode = !isEditMode;
    await loadContacts();
  });
}

if (addContactButton) {
  addContactButton.addEventListener('click', async function () {
    if (deleteMode) {
      setDeleteMode(false);
      deleteContactsButton.textContent = 'Delete Contacts';
      selectedContactIds.clear();
    }

    if (isEditMode) {
      isEditMode = false;
    }

    isAddMode = !isAddMode;
    await loadContacts();

    if (isAddMode) {
      const saveButton = document.getElementById('saveNewContactButton');
      if (saveButton) {
        saveButton.addEventListener('click', saveNewContact);
      }
    }
  });
}

if (contactSearch) {
  contactSearch.addEventListener('input', function () {
    loadContacts(contactSearch.value);
  });
}

if (contactsList || managerStatus) {
  loadContacts();
}

if (contactsList) {
  contactsList.addEventListener('change', function (event) {
    if (!event.target.classList.contains('contact-select')) {
      return;
    }

    const id = Number(event.target.dataset.id);
    if (event.target.checked) {
      selectedContactIds.add(id);
    } else {
      selectedContactIds.delete(id);
    }
  });

  contactsList.addEventListener('click', async function (event) {
    const cancelContactButton = event.target.closest('#cancelContactButton');
    if (cancelContactButton) {
      isAddMode = false;
      await loadContacts();
      return;
    }
    const cancelEditButton = event.target.closest('.cancel-edit-button');
    if (cancelEditButton) {
      isEditMode = false;
      await loadContacts();
      return;
    }
    const saveEditButton = event.target.closest('.save-edit-button');
    if (!saveEditButton) {
      return;
    }

    const id = Number(saveEditButton.dataset.id);
    const row = saveEditButton.closest('tr');
    const firstName = row.querySelector('[data-field="firstName"]')?.value.trim();
    const lastName = row.querySelector('[data-field="lastName"]')?.value.trim();
    const phone = row.querySelector('[data-field="phone"]')?.value.trim();
    const email = row.querySelector('[data-field="email"]')?.value.trim();

    if (!firstName || !lastName) {
      alert('First name and last name are required.');
      return;
    }

    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
      alert('Please log in to update contacts.');
      return;
    }

    let user;
    try {
      user = JSON.parse(storedUser);
    } catch (error) {
      alert('Your session is invalid. Please log in again.');
      return;
    }

    try {
      const response = await fetch('LAMPAPI/EditData.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          UserID: user.id,
          ID: id,
          FirstName: firstName,
          LastName: lastName,
          Phone: phone,
          Email: email
        })
      });

      const result = await parseJsonResponse(response);

      if (!response.ok || result.error) {
        throw new Error(result.error || 'Failed to update contact.');
      }

      isEditMode = false;
      await loadContacts();
      alert('Contact updated successfully.');
    } catch (error) {
      if (managerStatus) {
        managerStatus.textContent = error.message;
        managerStatus.classList.add('text-danger');
      }
      alert(error.message);
    }
  });
}
