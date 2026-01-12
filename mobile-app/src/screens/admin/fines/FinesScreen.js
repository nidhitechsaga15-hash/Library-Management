import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Modal,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import SearchableDropdown from '../../../components/SearchableDropdown';

export default function FinesScreen({ navigation }) {
  const [fines, setFines] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [updateModalVisible, setUpdateModalVisible] = useState(false);
  const [selectedFine, setSelectedFine] = useState(null);
  const [newStatus, setNewStatus] = useState('');

  useEffect(() => {
    loadFines();
  }, []);

  const loadFines = async () => {
    try {
      const data = await apiService.admin.getFines();
      setFines(data);
    } catch (error) {
      console.error('Error loading fines:', error);
      Alert.alert('Error', 'Failed to load fines');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadFines();
  };

  const handleUpdateStatus = (fine) => {
    setSelectedFine(fine);
    setNewStatus(fine.status);
    setUpdateModalVisible(true);
  };

  const handleSubmitUpdate = async () => {
    if (!newStatus) {
      Alert.alert('Error', 'Please select a status');
      return;
    }

    try {
      await apiService.admin.updateFineStatus(selectedFine.id, newStatus);
      Alert.alert('Success', 'Fine status updated successfully');
      setUpdateModalVisible(false);
      loadFines();
    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Failed to update fine status');
    }
  };

  const renderFine = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => handleUpdateStatus(item)}
    >
      <View style={styles.cardContent}>
        <View style={styles.amountRow}>
          <Text style={styles.amount}>₹{item.amount}</Text>
          <Text style={[styles.status, styles[`status${item.status}`]]}>
            {item.status?.toUpperCase()}
          </Text>
        </View>
        <Text style={styles.userName}>User: {item.user?.name}</Text>
        <Text style={styles.userEmail}>{item.user?.email}</Text>
        {item.borrow?.book && (
          <Text style={styles.bookName}>Book: {item.borrow.book.title}</Text>
        )}
        <Text style={styles.reason}>Reason: {item.reason || 'N/A'}</Text>
        <Text style={styles.date}>
          Created: {new Date(item.created_at).toLocaleDateString()}
        </Text>
      </View>
      <Ionicons name="chevron-forward" size={20} color="#999" />
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
        <Text style={styles.headerTitle}>Fines</Text>
      </View>

      <FlatList
        data={fines}
        renderItem={renderFine}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={styles.listContent}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No fines found</Text>
          </View>
        }
      />

      <Modal
        visible={updateModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setUpdateModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Update Fine Status</Text>
              <TouchableOpacity onPress={() => setUpdateModalVisible(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody}>
              {selectedFine && (
                <>
                  <View style={styles.fineInfo}>
                    <Text style={styles.fineInfoLabel}>Amount:</Text>
                    <Text style={styles.fineInfoValue}>₹{selectedFine.amount}</Text>
                  </View>
                  <View style={styles.fineInfo}>
                    <Text style={styles.fineInfoLabel}>User:</Text>
                    <Text style={styles.fineInfoValue}>{selectedFine.user?.name}</Text>
                  </View>
                  <View style={styles.fineInfo}>
                    <Text style={styles.fineInfoLabel}>Current Status:</Text>
                    <Text style={styles.fineInfoValue}>{selectedFine.status?.toUpperCase()}</Text>
                  </View>
                </>
              )}

              <View style={styles.formGroup}>
                <Text style={styles.label}>New Status *</Text>
                <SearchableDropdown
                  options={[
                    { label: 'Pending', value: 'pending' },
                    { label: 'Paid', value: 'paid' },
                    { label: 'Waived', value: 'waived' },
                  ]}
                  selectedValue={newStatus}
                  onSelect={(value) => setNewStatus(value)}
                  placeholder="Select status"
                />
              </View>
            </ScrollView>

            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={[styles.button, styles.cancelButton]}
                onPress={() => setUpdateModalVisible(false)}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.button, styles.submitButton]}
                onPress={handleSubmitUpdate}
              >
                <Text style={styles.submitButtonText}>Update</Text>
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
  },
  headerTitle: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  listContent: {
    padding: 15,
  },
  card: {
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
    alignItems: 'center',
  },
  cardContent: {
    flex: 1,
  },
  amountRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  amount: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
  },
  status: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
    fontSize: 11,
    fontWeight: '600',
  },
  statuspending: {
    backgroundColor: '#fff3cd',
    color: '#856404',
  },
  statuspaid: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  statuswaived: {
    backgroundColor: '#d1ecf1',
    color: '#0c5460',
  },
  userName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 3,
  },
  userEmail: {
    fontSize: 12,
    color: '#666',
    marginBottom: 8,
  },
  bookName: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  reason: {
    fontSize: 13,
    color: '#999',
    marginBottom: 5,
  },
  date: {
    fontSize: 11,
    color: '#999',
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
  fineInfo: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 15,
    paddingBottom: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  fineInfoLabel: {
    fontSize: 14,
    color: '#666',
  },
  fineInfoValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
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
  cancelButton: {
    backgroundColor: '#e0e0e0',
  },
  cancelButtonText: {
    color: '#333',
    fontWeight: '600',
  },
  submitButton: {
    backgroundColor: '#667eea',
  },
  submitButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
});
