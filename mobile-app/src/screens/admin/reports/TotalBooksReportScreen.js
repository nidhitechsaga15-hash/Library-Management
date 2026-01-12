import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  FlatList,
  TextInput,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import SearchableDropdown from '../../../components/SearchableDropdown';
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

export default function TotalBooksReportScreen({ navigation }) {
  const [books, setBooks] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [categories, setCategories] = useState([]);
  const [showSearchModal, setShowSearchModal] = useState(false);
  const [filters, setFilters] = useState({
    category_id: null,
    status: null,
  });

  useEffect(() => {
    loadCategories();
    loadData();
  }, []);

  const loadCategories = async () => {
    try {
      const data = await apiService.admin.getCategories();
      setCategories(data.map(cat => ({ label: cat.name, value: cat.id })));
    } catch (error) {
      console.error('Error loading categories:', error);
    }
  };

  const loadData = async (searchFilters = {}) => {
    try {
      const token = await AsyncStorage.getItem('token');
      const params = new URLSearchParams();
      if (searchFilters.category_id) params.append('category_id', searchFilters.category_id);
      if (searchFilters.status) params.append('status', searchFilters.status);
      
      const queryString = params.toString();
      const url = `http://192.168.1.45:8000/api/mobile/admin/reports/total-books${queryString ? '?' + queryString : ''}`;
      const response = await axios.get(url, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      
      setBooks(response.data.books || []);
      setStats(response.data.stats || {});
    } catch (error) {
      console.error('Error loading report:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const handleSearch = () => {
    setLoading(true);
    loadData(filters);
    setShowSearchModal(false);
  };

  const handleReset = () => {
    setFilters({ category_id: null, status: null });
    setLoading(true);
    loadData({});
    setShowSearchModal(false);
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const renderBook = ({ item }) => (
    <View style={styles.card}>
      <Text style={styles.bookTitle}>{item.title}</Text>
      <Text style={styles.bookIsbn}>ISBN: {item.isbn}</Text>
      <View style={styles.bookDetails}>
        <Text style={styles.bookDetail}>Author: {item.author?.name || 'N/A'}</Text>
        <Text style={styles.bookDetail}>Category: {item.category?.name || 'N/A'}</Text>
        <Text style={styles.bookDetail}>
          Copies: {item.available_copies}/{item.total_copies}
        </Text>
        <Text style={[styles.status, item.status === 'available' ? styles.statusAvailable : styles.statusUnavailable]}>
          {item.status?.toUpperCase()}
        </Text>
      </View>
    </View>
  );

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#667eea" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Total Books Report</Text>
        <TouchableOpacity onPress={() => setShowSearchModal(true)}>
          <Ionicons name="search" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      <ScrollView
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      >
        {stats && (
          <View style={styles.statsContainer}>
            <View style={styles.statCard}>
              <Text style={styles.statValue}>{stats.total_books || 0}</Text>
              <Text style={styles.statLabel}>Total Books</Text>
            </View>
            <View style={styles.statCard}>
              <Text style={[styles.statValue, { color: '#28a745' }]}>
                {stats.available_books || 0}
              </Text>
              <Text style={styles.statLabel}>Available</Text>
            </View>
            <View style={styles.statCard}>
              <Text style={[styles.statValue, { color: '#dc3545' }]}>
                {stats.unavailable_books || 0}
              </Text>
              <Text style={styles.statLabel}>Unavailable</Text>
            </View>
          </View>
        )}

        <View style={styles.listHeader}>
          <Text style={styles.listHeaderText}>All Books ({books.length})</Text>
        </View>

        <FlatList
          data={books}
          renderItem={renderBook}
          keyExtractor={(item) => item.id.toString()}
          scrollEnabled={false}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No books found</Text>
            </View>
          }
        />
      </ScrollView>

      {/* Search Modal */}
      <Modal
        visible={showSearchModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowSearchModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Search & Filter</Text>
              <TouchableOpacity onPress={() => setShowSearchModal(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody}>
              <View style={styles.formGroup}>
                <Text style={styles.label}>Category</Text>
                <SearchableDropdown
                  options={[{ label: 'All Categories', value: null }, ...categories]}
                  selectedValue={filters.category_id}
                  onSelect={(value) => setFilters({ ...filters, category_id: value })}
                  placeholder="Select Category"
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Status</Text>
                <SearchableDropdown
                  options={[
                    { label: 'All Status', value: null },
                    { label: 'Available', value: 'available' },
                    { label: 'Unavailable', value: 'unavailable' },
                  ]}
                  selectedValue={filters.status}
                  onSelect={(value) => setFilters({ ...filters, status: value })}
                  placeholder="Select Status"
                />
              </View>
            </ScrollView>

            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={[styles.button, styles.resetButton]}
                onPress={handleReset}
              >
                <Text style={styles.resetButtonText}>Reset</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.button, styles.searchButton]}
                onPress={handleSearch}
              >
                <Text style={styles.searchButtonText}>Search</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
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
  statsContainer: {
    flexDirection: 'row',
    padding: 15,
    gap: 10,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  statValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#667eea',
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
  },
  listHeader: {
    paddingHorizontal: 15,
    paddingVertical: 10,
    backgroundColor: '#f5f5f5',
  },
  listHeaderText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginHorizontal: 15,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  bookTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  bookIsbn: {
    fontSize: 14,
    color: '#666',
    marginBottom: 10,
  },
  bookDetails: {
    marginTop: 10,
  },
  bookDetail: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  status: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
    fontSize: 11,
    fontWeight: '600',
    alignSelf: 'flex-start',
    marginTop: 10,
  },
  statusAvailable: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  statusUnavailable: {
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
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 16,
    width: '90%',
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  modalBody: {
    padding: 20,
  },
  formGroup: {
    marginBottom: 15,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  modalFooter: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    padding: 20,
    borderTopWidth: 1,
    borderTopColor: '#e0e0e0',
    gap: 10,
  },
  button: {
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
  },
  resetButton: {
    backgroundColor: '#e0e0e0',
  },
  resetButtonText: {
    color: '#333',
    fontWeight: '600',
  },
  searchButton: {
    backgroundColor: '#667eea',
  },
  searchButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
});

