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
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import SearchableDropdown from '../../../components/SearchableDropdown';
import DateTimePicker from '@react-native-community/datetimepicker';
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

export default function BookIssueReportScreen({ navigation }) {
  const [borrows, setBorrows] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [showSearchModal, setShowSearchModal] = useState(false);
  const [filters, setFilters] = useState({
    status: null,
    from_date: null,
    to_date: null,
  });
  const [showFromDatePicker, setShowFromDatePicker] = useState(false);
  const [showToDatePicker, setShowToDatePicker] = useState(false);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async (searchFilters = {}) => {
    try {
      const token = await AsyncStorage.getItem('token');
      const params = new URLSearchParams();
      if (searchFilters.status) params.append('status', searchFilters.status);
      if (searchFilters.from_date) params.append('from_date', searchFilters.from_date);
      if (searchFilters.to_date) params.append('to_date', searchFilters.to_date);
      
      const queryString = params.toString();
      const url = `http://192.168.1.45:8000/api/mobile/admin/reports/book-issue${queryString ? '?' + queryString : ''}`;
      const response = await axios.get(url, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      
      setBorrows(response.data.borrows || []);
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
    setFilters({ status: null, from_date: null, to_date: null });
    setLoading(true);
    loadData({});
    setShowSearchModal(false);
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const renderBorrow = ({ item }) => (
    <View style={styles.card}>
      <Text style={styles.title}>{item.book?.title}</Text>
      <Text style={styles.subtitle}>Borrowed by: {item.user?.name}</Text>
      <View style={styles.details}>
        <Text style={styles.detail}>
          Borrow Date: {new Date(item.borrow_date).toLocaleDateString()}
        </Text>
        <Text style={styles.detail}>
          Due Date: {new Date(item.due_date).toLocaleDateString()}
        </Text>
        <Text style={[styles.status, item.status === 'borrowed' ? styles.statusBorrowed : styles.statusReturned]}>
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
        <Text style={styles.headerTitle}>Book Issue Report</Text>
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
              <Text style={[styles.statValue, { color: '#667eea' }]}>
                {stats.total_issued || 0}
              </Text>
              <Text style={styles.statLabel}>Total Issued</Text>
            </View>
            <View style={styles.statCard}>
              <Text style={[styles.statValue, { color: '#28a745' }]}>
                {stats.total_returned || 0}
              </Text>
              <Text style={styles.statLabel}>Total Returned</Text>
            </View>
          </View>
        )}

        <View style={styles.listHeader}>
          <Text style={styles.listHeaderText}>All Issues ({borrows.length})</Text>
        </View>

        <FlatList
          data={borrows}
          renderItem={renderBorrow}
          keyExtractor={(item) => item.id.toString()}
          scrollEnabled={false}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No borrows found</Text>
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
                <Text style={styles.label}>Status</Text>
                <SearchableDropdown
                  options={[
                    { label: 'All Status', value: null },
                    { label: 'Borrowed', value: 'borrowed' },
                    { label: 'Returned', value: 'returned' },
                  ]}
                  selectedValue={filters.status}
                  onSelect={(value) => setFilters({ ...filters, status: value })}
                  placeholder="Select Status"
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>From Date</Text>
                <TouchableOpacity
                  style={styles.dateButton}
                  onPress={() => setShowFromDatePicker(true)}
                >
                  <Text style={filters.from_date ? styles.dateText : styles.placeholderText}>
                    {filters.from_date || 'Select from date'}
                  </Text>
                  <Ionicons name="calendar-outline" size={20} color="#667eea" />
                </TouchableOpacity>
                {showFromDatePicker && (
                  <DateTimePicker
                    value={filters.from_date ? new Date(filters.from_date) : new Date()}
                    mode="date"
                    display="default"
                    onChange={(event, selectedDate) => {
                      setShowFromDatePicker(false);
                      if (selectedDate) {
                        setFilters({ ...filters, from_date: selectedDate.toISOString().split('T')[0] });
                      }
                    }}
                  />
                )}
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>To Date</Text>
                <TouchableOpacity
                  style={styles.dateButton}
                  onPress={() => setShowToDatePicker(true)}
                >
                  <Text style={filters.to_date ? styles.dateText : styles.placeholderText}>
                    {filters.to_date || 'Select to date'}
                  </Text>
                  <Ionicons name="calendar-outline" size={20} color="#667eea" />
                </TouchableOpacity>
                {showToDatePicker && (
                  <DateTimePicker
                    value={filters.to_date ? new Date(filters.to_date) : new Date()}
                    mode="date"
                    display="default"
                    onChange={(event, selectedDate) => {
                      setShowToDatePicker(false);
                      if (selectedDate) {
                        setFilters({ ...filters, to_date: selectedDate.toISOString().split('T')[0] });
                      }
                    }}
                  />
                )}
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
    backgroundColor: '#28a745',
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
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 14,
    color: '#666',
    marginBottom: 10,
  },
  details: {
    marginTop: 10,
  },
  detail: {
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
  statusBorrowed: {
    backgroundColor: '#d1ecf1',
    color: '#0c5460',
  },
  statusReturned: {
    backgroundColor: '#d4edda',
    color: '#155724',
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
  dateButton: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 12,
    borderWidth: 1,
    borderColor: '#e0e0e0',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  dateText: {
    fontSize: 16,
    color: '#333',
  },
  placeholderText: {
    fontSize: 16,
    color: '#999',
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
    backgroundColor: '#28a745',
  },
  searchButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
});

