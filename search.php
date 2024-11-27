<?php
include 'session_manager.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users - Lumi Social</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .search-button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-results {
            margin-top: 20px;
        }
        .user-card {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            cursor: pointer;
        }
        .user-card:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Search Users</h2>
        <div class="search-form">
            <input type="text" id="searchInput" class="search-input" placeholder="Search users...">
            <button onclick="searchUsers()" class="search-button">Search</button>
        </div>
        <div id="searchResults" class="search-results"></div>
    </div>

    <script>
        function searchUsers() {
            const query = document.getElementById('searchInput').value;
            fetch('search_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'query=' + encodeURIComponent(query)
            })
            .then(response => response.json())
            .then(users => {
                const resultsDiv = document.getElementById('searchResults');
                resultsDiv.innerHTML = '';
                users.forEach(user => {
                    const userCard = document.createElement('div');
                    userCard.className = 'user-card';
                    userCard.innerHTML = `<h3>${user.username}</h3>`;
                    userCard.onclick = () => {
                        window.location.href = `profile.php?id=${user.id}`;
                    };
                    resultsDiv.appendChild(userCard);
                });
            });
        }
    </script>
</body>
</html>