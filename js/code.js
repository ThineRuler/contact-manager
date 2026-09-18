const registerForm = document.getElementById('registerForm');
const registerMessage = document.getElementById('registerMessage');
const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');
const contactsList = document.getElementById('contactsList');
const managerStatus = document.getElementById('managerStatus');

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

async function loadContacts() {
  if (!contactsList) {
    return;
  }

  const storedUser = localStorage.getItem('user');
  if (!storedUser) {
    contactsList.innerHTML = '<p>Please log in to view your contacts.</p>';
    return;
  }

  let user;
  try {
    user = JSON.parse(storedUser);
  } catch (error) {
    contactsList.innerHTML = '<p>Your session is invalid. Please log in again.</p>';
    return;
  }

  if (!user.id) {
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
        Search: '',
        Page: 1,
        ResultsPerPage: 50
      })
    });

    const result = await parseJsonResponse(response);

    if (!response.ok || result.error) {
      throw new Error(result.error || 'Failed to load contacts.');
    }

    const contacts = result.results || [];

    if (!contacts.length) {
      contactsList.innerHTML = '<p>No contacts found.</p>';
      return;
    }

    contactsList.innerHTML = `
      <table class="contact-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Phone Number</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody>
          ${contacts.map(contact => `
            <tr>
              <td>${contact.firstName} ${contact.lastName}</td>
              <td>${contact.phone || 'N/A'}</td>
              <td>${contact.email || 'N/A'}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  } catch (error) {
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

    const formData = {
      FirstName: document.getElementById('firstName').value.trim(),
      LastName: document.getElementById('lastName').value.trim(),
      Login: document.getElementById('login').value.trim(),
      Password: document.getElementById('password').value
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

if (contactsList || managerStatus) {
  loadContacts();
}
