import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  TextInput,
  Alert,
  Animated,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';

export default function BooksScreen({ navigation }) {
  const [books, setBooks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [searchTimeout, setSearchTimeout] = useState(null);
  const shimmerAnim = useRef(new Animated.Value(0)).current;
  const isInitialMount = useRef(true);

  useEffect(() => {
    loadBooks(1, true);
  }, []);

  useEffect(() => {
    if (loading) {
      Animated.loop(
        Animated.sequence([
          Animated.timing(shimmerAnim, {
            toValue: 1,
            duration: 1000,
            useNativeDriver: true,
          }),
          Animated.timing(shimmerAnim, {
            toValue: 0,
            duration: 1000,
            useNativeDriver: true,
          }),
        ])
      ).start();
    }
  }, [loading]);

  // Debounced search - only trigger when searchQuery changes after initial load
  useEffect(() => {
    // Skip on initial mount
    if (isInitialMount.current) {
      isInitialMount.current = false;
      return;
    }

    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }
    
    const timeout = setTimeout(() => {
      loadBooks(1, true, searchQuery);
    }, 500);

    setSearchTimeout(timeout);

    return () => {
      if (timeout) clearTimeout(timeout);
    };
  }, [searchQuery]);

  const loadBooks = async (page = 1, reset = false, search = '') => {
    try {
      if (reset) {
        setLoading(true);
        setBooks([]);
      } else {
        setLoadingMore(true);
      }

      const response = await apiService.admin.getBooks(page, 100, search);
      
      if (reset) {
        setBooks(response.books);
      } else {
        // Filter out duplicates by book ID
        setBooks(prev => {
          const existingIds = new Set(prev.map(book => book.id));
          const newBooks = response.books.filter(book => !existingIds.has(book.id));
          return [...prev, ...newBooks];
        });
      }
      
      setCurrentPage(page);
      setHasMore(response.pagination.has_more);
    } catch (error) {
      console.error('Error loading books:', error);
      Alert.alert('Error', 'Failed to load books');
    } finally {
      setLoading(false);
      setRefreshing(false);
      setLoadingMore(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadBooks(1, true, searchQuery);
  };

  const loadMore = () => {
    if (!loadingMore && hasMore) {
      loadBooks(currentPage + 1, false, searchQuery);
    }
  };

  // Use books directly from API (server-side search already applied)
  const filteredBooks = books;

  const handleDelete = async (book) => {
    Alert.alert(
      'Delete Book',
      `Are you sure you want to delete "${book.title}"?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              await apiService.admin.deleteBook(book.id);
              Alert.alert('Success', 'Book deleted successfully');
              loadBooks(1, true, searchQuery);
            } catch (error) {
              Alert.alert('Error', error.response?.data?.message || 'Failed to delete book');
            }
          },
        },
      ]
    );
  };

  const renderSkeletonBook = () => {
    const opacity = shimmerAnim.interpolate({
      inputRange: [0, 1],
      outputRange: [0.3, 0.7],
    });

    return (
      <Animated.View style={[styles.bookCard, { opacity }]}>
        <View style={styles.bookInfo}>
          <View style={[styles.skeletonBox, { width: '80%', height: 18, marginBottom: 8, borderRadius: 4 }]} />
          <View style={[styles.skeletonBox, { width: '60%', height: 14, marginBottom: 6, borderRadius: 4 }]} />
          <View style={[styles.skeletonBox, { width: '40%', height: 12, marginBottom: 10, borderRadius: 4 }]} />
          <View style={styles.bookMeta}>
            <View style={[styles.skeletonBox, { width: 100, height: 14, borderRadius: 4 }]} />
            <View style={[styles.skeletonBox, { width: 60, height: 24, borderRadius: 12 }]} />
          </View>
        </View>
        <View style={styles.actionButtons}>
          <View style={[styles.skeletonBox, { width: 32, height: 32, borderRadius: 16 }]} />
          <View style={[styles.skeletonBox, { width: 32, height: 32, borderRadius: 16 }]} />
        </View>
      </Animated.View>
    );
  };

  const renderFooter = () => {
    if (!loadingMore) return null;
    return (
      <View style={styles.footerLoader}>
        <ActivityIndicator size="small" color="#667eea" />
      </View>
    );
  };

  const renderBook = ({ item }) => (
    <TouchableOpacity
      style={styles.bookCard}
      onPress={() => navigation.navigate('More', { screen: 'BookDetails', params: { bookId: item.id } })}
    >
      <View style={styles.bookInfo}>
        <Text style={styles.bookTitle}>{item.title}</Text>
        <Text style={styles.bookAuthor}>By {item.author}</Text>
        <Text style={styles.bookCategory}>{item.category}</Text>
        <View style={styles.bookMeta}>
          <Text style={styles.bookMetaText}>
            Available: {item.available_copies}/{item.total_copies}
          </Text>
          <Text style={[styles.statusBadge, item.status === 'available' ? styles.available : styles.unavailable]}>
            {item.status}
          </Text>
        </View>
      </View>
      <View style={styles.actionButtons}>
        <TouchableOpacity
          style={styles.editButton}
          onPress={() => navigation.navigate('More', { screen: 'EditBook', params: { bookId: item.id } })}
        >
          <Ionicons name="create-outline" size={20} color="#667eea" />
        </TouchableOpacity>
        <TouchableOpacity
          style={styles.deleteButton}
          onPress={() => handleDelete(item)}
        >
          <Ionicons name="trash-outline" size={20} color="#dc3545" />
        </TouchableOpacity>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Books</Text>
        <TouchableOpacity
          style={styles.addButton}
          onPress={() => navigation.navigate('More', { screen: 'CreateBook' })}
        >
          <Text style={styles.addButtonText}>+ Add</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.searchContainer}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search books..."
          value={searchQuery}
          onChangeText={setSearchQuery}
        />
      </View>

      {loading && books.length === 0 ? (
        <FlatList
          data={[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]}
          renderItem={() => renderSkeletonBook()}
          keyExtractor={(item) => item.toString()}
          contentContainerStyle={styles.listContent}
        />
      ) : (
        <FlatList
          data={filteredBooks}
          renderItem={renderBook}
          keyExtractor={(item, index) => `book-${item.id}-${index}`}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          contentContainerStyle={styles.listContent}
          onEndReached={loadMore}
          onEndReachedThreshold={0.5}
          ListFooterComponent={renderFooter}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No books found</Text>
            </View>
          }
        />
      )}
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
    fontSize: 24,
    fontWeight: 'bold',
  },
  addButton: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderRadius: 8,
  },
  addButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
  searchContainer: {
    padding: 15,
    backgroundColor: '#fff',
  },
  searchInput: {
    backgroundColor: '#f5f5f5',
    borderRadius: 10,
    padding: 12,
    fontSize: 16,
  },
  listContent: {
    padding: 15,
  },
  bookCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButtons: {
    flexDirection: 'row',
    gap: 10,
    alignItems: 'center',
  },
  editButton: {
    padding: 8,
  },
  deleteButton: {
    padding: 8,
  },
  bookInfo: {
    flex: 1,
  },
  bookTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  bookAuthor: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  bookCategory: {
    fontSize: 12,
    color: '#999',
    marginBottom: 10,
  },
  bookMeta: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  bookMetaText: {
    fontSize: 14,
    color: '#666',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
    fontSize: 12,
    fontWeight: '600',
  },
  available: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  unavailable: {
    backgroundColor: '#f8d7da',
    color: '#721c24',
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
  },
  skeletonBox: {
    backgroundColor: '#e0e0e0',
  },
  footerLoader: {
    paddingVertical: 20,
    alignItems: 'center',
  },
});

