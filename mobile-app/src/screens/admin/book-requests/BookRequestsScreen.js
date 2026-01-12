import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Modal,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import DateTimePicker from '@react-native-community/datetimepicker';

export default function BookRequestsScreen({ navigation }) {
  const [requests, setRequests] = useState([]);
  const [stats, setStats] = useState({
    pending_count: 0,
    approved_count: 0,
    total_requests: 0,
    issued_count: 0,
  });
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [issueModalVisible, setIssueModalVisible] = useState(false);
  const [selectedRequest, setSelectedRequest] = useState(null);
  const [dueDate, setDueDate] = useState(new Date(Date.now() + 14 * 24 * 60 * 60 * 1000));
  const [showDatePicker, setShowDatePicker] = useState(false);

  useEffect(() => {
    loadRequests();
  }, []);

  const loadRequests = async () => {
    try {
      const response = await apiService.admin.getBookRequests();
      setRequests(response.requests || []);
      setStats(response.stats || {
        pending_count: 0,
        approved_count: 0,
        total_requests: 0,
        issued_count: 0,
      });
    } catch (error) {
      console.error('Error loading requests:', error);
      Alert.alert('Error', 'Failed to load book requests');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadRequests();
  };

  const handleApprove = async (request) => {
    Alert.alert(
      'Approve Request',
      `Approve request for "${request.book?.title}" by ${request.user?.name}?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            try {
              await apiService.admin.approveRequest(request.id);
              Alert.alert('Success', 'Request approved successfully');
              loadRequests();
            } catch (error) {
              Alert.alert('Error', error.response?.data?.message || 'Failed to approve request');
            }
          },
        },
      ]
    );
  };

  const handleReject = async (request) => {
    Alert.alert(
      'Reject Request',
      `Reject request for "${request.book?.title}" by ${request.user?.name}?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Reject',
          style: 'destructive',
          onPress: async () => {
            try {
              await apiService.admin.rejectRequest(request.id);
              Alert.alert('Success', 'Request rejected');
              loadRequests();
            } catch (error) {
              Alert.alert('Error', error.response?.data?.message || 'Failed to reject request');
            }
          },
        },
      ]
    );
  };

  const handleIssue = async () => {
    if (!selectedRequest) return;

    try {
      await apiService.admin.issueRequest(selectedRequest.id, dueDate.toISOString().split('T')[0]);
      Alert.alert('Success', 'Book issued successfully');
      setIssueModalVisible(false);
      setSelectedRequest(null);
      loadRequests();
    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Failed to issue book');
    }
  };

  const openIssueModal = (request) => {
    if (request.status !== 'approved') {
      Alert.alert('Error', 'Request must be approved first');
      return;
    }
    setSelectedRequest(request);
    setDueDate(new Date(Date.now() + 14 * 24 * 60 * 60 * 1000));
    setIssueModalVisible(true);
  };

  const renderRequest = ({ item }) => (
    <View style={styles.card}>
      <View style={styles.cardContent}>
        <Text style={styles.title}>{item.book?.title}</Text>
        <Text style={styles.subtitle}>Requested by: {item.user?.name}</Text>
        <Text style={styles.email}>{item.user?.email}</Text>
        <Text style={styles.date}>
          Requested: {new Date(item.created_at).toLocaleDateString()}
        </Text>
        <Text style={[styles.status, styles[`status${item.status}`]]}>
          {item.status?.toUpperCase()}
        </Text>
      </View>
      <View style={styles.actionButtons}>
        {item.status === 'pending' && (
          <>
            <TouchableOpacity
              style={styles.approveButton}
              onPress={() => handleApprove(item)}
            >
              <Ionicons name="checkmark-circle" size={24} color="#28a745" />
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.rejectButton}
              onPress={() => handleReject(item)}
            >
              <Ionicons name="close-circle" size={24} color="#dc3545" />
            </TouchableOpacity>
          </>
        )}
        {item.status === 'approved' && (
          <TouchableOpacity
            style={styles.issueButton}
            onPress={() => openIssueModal(item)}
          >
            <Ionicons name="book" size={24} color="#667eea" />
          </TouchableOpacity>
        )}
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
        <Text style={styles.headerTitle}>Book Requests</Text>
      </View>

      <ScrollView
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      >
        {/* Statistics Cards */}
        <View style={styles.statsContainer}>
          <View style={[styles.statCard, styles.statCardWarning]}>
            <View style={styles.statContent}>
              <Text style={styles.statLabel}>Pending Requests</Text>
              <Text style={[styles.statValue, styles.statValueWarning]}>
                {stats.pending_count}
              </Text>
            </View>
            <View style={[styles.statIcon, styles.statIconWarning]}>
              <Ionicons name="time-outline" size={24} color="#fff" />
            </View>
          </View>

          <View style={[styles.statCard, styles.statCardSuccess]}>
            <View style={styles.statContent}>
              <Text style={styles.statLabel}>Approved Requests</Text>
              <Text style={[styles.statValue, styles.statValueSuccess]}>
                {stats.approved_count}
              </Text>
            </View>
            <View style={[styles.statIcon, styles.statIconSuccess]}>
              <Ionicons name="checkmark-circle" size={24} color="#fff" />
            </View>
          </View>

          <View style={[styles.statCard, styles.statCardInfo]}>
            <View style={styles.statContent}>
              <Text style={styles.statLabel}>Total Requests</Text>
              <Text style={[styles.statValue, styles.statValueInfo]}>
                {stats.total_requests}
              </Text>
            </View>
            <View style={[styles.statIcon, styles.statIconInfo]}>
              <Ionicons name="list" size={24} color="#fff" />
            </View>
          </View>

          <View style={[styles.statCard, styles.statCardPrimary]}>
            <View style={styles.statContent}>
              <Text style={styles.statLabel}>Issued</Text>
              <Text style={[styles.statValue, styles.statValuePrimary]}>
                {stats.issued_count}
              </Text>
            </View>
            <View style={[styles.statIcon, styles.statIconPrimary]}>
              <Ionicons name="book" size={24} color="#fff" />
            </View>
          </View>
        </View>

        {/* Requests List */}
        <View style={styles.listHeader}>
          <Text style={styles.listHeaderText}>All Requests</Text>
        </View>

        {requests.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No book requests found</Text>
          </View>
        ) : (
          requests.map((item) => (
            <View key={item.id.toString()}>{renderRequest({ item })}</View>
          ))
        )}
      </ScrollView>

      <Modal
        visible={issueModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setIssueModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Issue Book</Text>
              <TouchableOpacity onPress={() => setIssueModalVisible(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody}>
              {selectedRequest && (
                <>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Book:</Text>
                    <Text style={styles.infoValue}>{selectedRequest.book?.title}</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Student:</Text>
                    <Text style={styles.infoValue}>{selectedRequest.user?.name}</Text>
                  </View>
                </>
              )}

              <View style={styles.formGroup}>
                <Text style={styles.label}>Due Date *</Text>
                <TouchableOpacity
                  style={styles.dateButton}
                  onPress={() => setShowDatePicker(true)}
                >
                  <Text style={styles.dateText}>
                    {dueDate.toLocaleDateString()}
                  </Text>
                  <Ionicons name="calendar" size={20} color="#667eea" />
                </TouchableOpacity>
                {showDatePicker && (
                  <DateTimePicker
                    value={dueDate}
                    mode="date"
                    display="default"
                    minimumDate={new Date()}
                    onChange={(event, selectedDate) => {
                      setShowDatePicker(false);
                      if (selectedDate) {
                        setDueDate(selectedDate);
                      }
                    }}
                  />
                )}
              </View>
            </ScrollView>

            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={[styles.button, styles.cancelButton]}
                onPress={() => setIssueModalVisible(false)}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.button, styles.submitButton]}
                onPress={handleIssue}
              >
                <Text style={styles.submitButtonText}>Issue Book</Text>
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
  statsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 15,
    gap: 10,
  },
  statCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    width: '47%',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderLeftWidth: 4,
  },
  statCardWarning: {
    borderLeftColor: '#ffc107',
  },
  statCardSuccess: {
    borderLeftColor: '#28a745',
  },
  statCardInfo: {
    borderLeftColor: '#17a2b8',
  },
  statCardPrimary: {
    borderLeftColor: '#667eea',
  },
  statContent: {
    flex: 1,
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
    marginBottom: 5,
  },
  statValue: {
    fontSize: 24,
    fontWeight: 'bold',
  },
  statValueWarning: {
    color: '#ffc107',
  },
  statValueSuccess: {
    color: '#28a745',
  },
  statValueInfo: {
    color: '#17a2b8',
  },
  statValuePrimary: {
    color: '#667eea',
  },
  statIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
  },
  statIconWarning: {
    backgroundColor: '#ffc107',
  },
  statIconSuccess: {
    backgroundColor: '#28a745',
  },
  statIconInfo: {
    backgroundColor: '#17a2b8',
  },
  statIconPrimary: {
    backgroundColor: '#667eea',
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
  listContent: {
    padding: 15,
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
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  cardContent: {
    flex: 1,
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 5,
    color: '#333',
  },
  subtitle: {
    fontSize: 14,
    color: '#666',
    marginBottom: 3,
  },
  email: {
    fontSize: 12,
    color: '#999',
    marginBottom: 8,
  },
  date: {
    fontSize: 12,
    color: '#666',
    marginBottom: 10,
  },
  status: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
    fontSize: 11,
    fontWeight: '600',
    alignSelf: 'flex-start',
  },
  statuspending: {
    backgroundColor: '#fff3cd',
    color: '#856404',
  },
  statusapproved: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  statusrejected: {
    backgroundColor: '#f8d7da',
    color: '#721c24',
  },
  statusissued: {
    backgroundColor: '#d1ecf1',
    color: '#0c5460',
  },
  actionButtons: {
    flexDirection: 'row',
    gap: 10,
    alignItems: 'center',
  },
  approveButton: {
    padding: 8,
  },
  rejectButton: {
    padding: 8,
  },
  issueButton: {
    padding: 8,
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
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 15,
    paddingBottom: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
  },
  infoValue: {
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
  dateButton: {
    backgroundColor: '#f5f5f5',
    borderRadius: 8,
    padding: 12,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#e0e0e0',
  },
  dateText: {
    fontSize: 16,
    color: '#333',
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
