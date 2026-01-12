import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, TextInput } from 'react-native';
import { apiService } from '../../../services/apiService';

export default function BooksScreen() {
  const [books, setBooks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  useEffect(() => {
    loadBooks();
  }, []);

  const loadBooks = async () => {
    try {
      const data = await apiService.student.getBooks();
      setBooks(data);
    } catch (error) {
      console.error('Error loading books:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadBooks();
  };

  const handleRequest = async (bookId) => {
    try {
      await apiService.student.requestBook(bookId);
      alert('Book request submitted successfully');
    } catch (error) {
      alert('Error requesting book: ' + error.message);
    }
  };

  const filteredBooks = books.filter(book =>
    book.title?.toLowerCase().includes(searchQuery.toLowerCase()) ||
    book.author?.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const renderBook = ({ item }) => (
    <View style={styles.card}>
      <Text style={styles.title}>{item.title}</Text>
      <Text style={styles.subtitle}>By {item.author}</Text>
      <Text style={styles.category}>{item.category}</Text>
      <Text style={styles.available}>Available: {item.available_copies}</Text>
      <TouchableOpacity style={styles.requestButton} onPress={() => handleRequest(item.id)}>
        <Text style={styles.requestButtonText}>Request Book</Text>
      </TouchableOpacity>
    </View>
  );

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#4299e1" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Books</Text>
      </View>
      <View style={styles.searchContainer}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search books..."
          value={searchQuery}
          onChangeText={setSearchQuery}
        />
      </View>
      <FlatList
        data={filteredBooks}
        renderItem={renderBook}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  header: { backgroundColor: '#4299e1', padding: 20, paddingTop: 50 },
  headerTitle: { color: '#fff', fontSize: 24, fontWeight: 'bold' },
  searchContainer: { padding: 15, backgroundColor: '#fff' },
  searchInput: { backgroundColor: '#f5f5f5', borderRadius: 10, padding: 12, fontSize: 16 },
  card: { backgroundColor: '#fff', padding: 15, margin: 15, borderRadius: 12 },
  title: { fontSize: 18, fontWeight: 'bold', marginBottom: 5 },
  subtitle: { fontSize: 14, color: '#666', marginBottom: 5 },
  category: { fontSize: 12, color: '#999', marginBottom: 5 },
  available: { fontSize: 14, color: '#4299e1', fontWeight: '600', marginBottom: 10 },
  requestButton: { backgroundColor: '#4299e1', padding: 10, borderRadius: 8, marginTop: 10 },
  requestButtonText: { color: '#fff', textAlign: 'center', fontWeight: '600' },
});

