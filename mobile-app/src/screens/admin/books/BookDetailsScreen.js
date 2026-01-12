import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';

export default function BookDetailsScreen({ navigation, route }) {
  const { bookId } = route.params;
  const [book, setBook] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadBook();
  }, []);

  const loadBook = async () => {
    try {
      const data = await apiService.admin.getBook(bookId);
      setBook(data);
    } catch (error) {
      Alert.alert('Error', 'Failed to load book details');
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#667eea" />
      </View>
    );
  }

  if (!book) {
    return (
      <View style={styles.centerContainer}>
        <Text style={styles.errorText}>Book not found</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Book Details</Text>
        <TouchableOpacity
          onPress={() => navigation.navigate('More', { screen: 'EditBook', params: { bookId: book.id } })}
        >
          <Ionicons name="create-outline" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.content}>
        <View style={styles.detailCard}>
          <Text style={styles.label}>Title</Text>
          <Text style={styles.value}>{book.title}</Text>
        </View>

        <View style={styles.detailCard}>
          <Text style={styles.label}>ISBN</Text>
          <Text style={styles.value}>{book.isbn}</Text>
        </View>

        <View style={styles.detailCard}>
          <Text style={styles.label}>Author</Text>
          <Text style={styles.value}>{book.author?.name || 'N/A'}</Text>
        </View>

        <View style={styles.detailCard}>
          <Text style={styles.label}>Category</Text>
          <Text style={styles.value}>{book.category?.name || 'N/A'}</Text>
        </View>

        {book.publisher && (
          <View style={styles.detailCard}>
            <Text style={styles.label}>Publisher</Text>
            <Text style={styles.value}>{book.publisher}</Text>
          </View>
        )}

        {book.edition && (
          <View style={styles.detailCard}>
            <Text style={styles.label}>Edition</Text>
            <Text style={styles.value}>{book.edition}</Text>
          </View>
        )}

        <View style={styles.detailCard}>
          <Text style={styles.label}>Total Copies</Text>
          <Text style={styles.value}>{book.total_copies}</Text>
        </View>

        <View style={styles.detailCard}>
          <Text style={styles.label}>Available Copies</Text>
          <Text style={styles.value}>{book.available_copies}</Text>
        </View>

        <View style={styles.detailCard}>
          <Text style={styles.label}>Status</Text>
          <Text style={[styles.statusBadge, book.status === 'available' ? styles.available : styles.unavailable]}>
            {book.status?.toUpperCase()}
          </Text>
        </View>

        {book.description && (
          <View style={styles.detailCard}>
            <Text style={styles.label}>Description</Text>
            <Text style={styles.value}>{book.description}</Text>
          </View>
        )}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: '#667eea',
    padding: 20,
    paddingTop: 50,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    padding: 15,
  },
  detailCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  label: {
    fontSize: 12,
    color: '#999',
    marginBottom: 5,
    fontWeight: '600',
  },
  value: {
    fontSize: 16,
    color: '#333',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
    fontSize: 12,
    fontWeight: '600',
    alignSelf: 'flex-start',
  },
  available: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  unavailable: {
    backgroundColor: '#f8d7da',
    color: '#721c24',
  },
  errorText: {
    fontSize: 16,
    color: '#999',
  },
});

