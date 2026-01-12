#!/bin/bash
# Create placeholder screens for all modules

# Admin screens
touch admin/authors/AuthorsScreen.js
touch admin/categories/CategoriesScreen.js
touch admin/users/UsersScreen.js
touch admin/fines/FinesScreen.js
touch admin/book-requests/BookRequestsScreen.js
touch admin/reports/ReportsScreen.js
touch admin/library-cards/LibraryCardsScreen.js

# Staff screens
touch staff/books/BooksScreen.js
touch staff/students/StudentsScreen.js
touch staff/borrows/BorrowsScreen.js
touch staff/fines/FinesScreen.js
touch staff/book-requests/BookRequestsScreen.js
touch staff/scanner/ScannerScreen.js
touch staff/library-cards/LibraryCardsScreen.js

# Student screens
touch student/books/BooksScreen.js
touch student/my-books/MyBooksScreen.js
touch student/fines/FinesScreen.js
touch student/library-card/LibraryCardScreen.js
touch student/reservations/ReservationsScreen.js

echo "Placeholder files created"
