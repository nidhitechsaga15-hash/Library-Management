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
  TextInput,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

export default function StudentWiseReportScreen({ navigation }) {
  const [students, setStudents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [showSearchModal, setShowSearchModal] = useState(false);
  const [filters, setFilters] = useState({
    name: '',
    student_id: '',
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async (searchFilters = {}) => {
    try {
      const token = await AsyncStorage.getItem('token');
      const params = new URLSearchParams();
      if (searchFilters.name) params.append('name', searchFilters.name);
      if (searchFilters.student_id) params.append('student_id', searchFilters.student_id);
      
      const queryString = params.toString();
      const url = `http://192.168.1.45:8000/api/mobile/admin/reports/student-wise${queryString ? '?' + queryString : ''}`;
      const response = await axios.get(url, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      
      setStudents(response.data.students || []);
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
    setFilters({ name: '', student_id: '' });
    setLoading(true);
    loadData({});
    setShowSearchModal(false);
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const handleStudentPress = async (studentId) => {
    try {
      console.log('Loading student detail for ID:', studentId);
      const response = await apiService.admin.getStudentDetailReport(studentId);
      console.log('Student detail response:', response);
      
      // Navigate to student detail screen with response data
      if (response && (response.student || response.success)) {
        navigation.navigate('StudentDetailReport', { studentData: response });
      } else {
        console.error('Invalid response structure:', response);
        Alert.alert('Error', 'Failed to load student details');
      }
    } catch (error) {
      console.error('Error loading student detail:', error);
      Alert.alert('Error', error.response?.data?.message || 'Failed to load student details');
    }
  };

  const renderStudent = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => handleStudentPress(item.id)}
    >
      <Text style={styles.title}>{item.name}</Text>
      <Text style={styles.subtitle}>{item.email}</Text>
      {item.student_id && <Text style={styles.studentId}>ID: {item.student_id}</Text>}
      <View style={styles.statsRow}>
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{item.total_borrows || 0}</Text>
          <Text style={styles.statLabel}>Total Borrows</Text>
        </View>
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{item.active_borrows || 0}</Text>
          <Text style={styles.statLabel}>Active</Text>
        </View>
        <View style={styles.statItem}>
          <Text style={[styles.statValue, { color: '#dc3545' }]}>
            ₹{item.pending_fines || 0}
          </Text>
          <Text style={styles.statLabel}>Pending Fines</Text>
        </View>
      </View>
    </TouchableOpacity>
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
        <Text style={styles.headerTitle}>Student-wise Report</Text>
        <TouchableOpacity onPress={() => setShowSearchModal(true)}>
          <Ionicons name="search" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      <ScrollView
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      >
        <View style={styles.listHeader}>
          <Text style={styles.listHeaderText}>All Students ({students.length})</Text>
        </View>

        <FlatList
          data={students}
          renderItem={renderStudent}
          keyExtractor={(item) => item.id.toString()}
          scrollEnabled={false}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No students found</Text>
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
                <Text style={styles.label}>Student Name</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Search by name"
                  value={filters.name}
                  onChangeText={(text) => setFilters({ ...filters, name: text })}
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Student ID</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Search by Student ID"
                  value={filters.student_id}
                  onChangeText={(text) => setFilters({ ...filters, student_id: text })}
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
    backgroundColor: '#17a2b8',
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
    marginBottom: 5,
  },
  studentId: {
    fontSize: 12,
    color: '#999',
    marginBottom: 15,
  },
  statsRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: 10,
    paddingTop: 15,
    borderTopWidth: 1,
    borderTopColor: '#e0e0e0',
  },
  statItem: {
    alignItems: 'center',
  },
  statValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#667eea',
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 11,
    color: '#666',
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
  input: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#e0e0e0',
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
    backgroundColor: '#17a2b8',
  },
  searchButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
});

