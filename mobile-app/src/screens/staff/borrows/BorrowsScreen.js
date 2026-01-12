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
  TextInput,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import SearchableDropdown from '../../../components/SearchableDropdown';
import DateTimePicker from '@react-native-community/datetimepicker';

export default function BorrowsScreen({ navigation }) {
  const [borrows, setBorrows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [issueModalVisible, setIssueModalVisible] = useState(false);
  const [users, setUsers] = useState([]);
  const [books, setBooks] = useState([]);
  const [issueForm, setIssueForm] = useState({
    user_id: '',
    book_id: '',
    borrow_date: new Date(),
    issue_duration_days: 15,
  });
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [calculatedDueDate, setCalculatedDueDate] = useState(null);

  useEffect(() => {
    loadBorrows();
  }, []);

  const calculateDueDate = (borrowDate, duration) => {
    const date = new Date(borrowDate);
    date.setDate(date.getDate() + 1); // Next day
    date.setDate(date.getDate() + (duration - 1)); // Add duration - 1 days
    return date;
  };

  useEffect(() => {
    if (issueForm.borrow_date && issueForm.issue_duration_days) {
      const dueDate = calculateDueDate(issueForm.borrow_date, issueForm.issue_duration_days);
      setCalculatedDueDate(dueDate);
    }
  }, [issueForm.borrow_date, issueForm.issue_duration_days]);

  const loadBorrows = async () => {
    try {
      const data = await apiService.staff.getBorrows();
      setBorrows(data);
    } catch (error) {
      console.error('Error loading borrows:', error);
      Alert.alert('Error', 'Failed to load borrows');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadBorrows();
  };

  const handleIssue = async () => {
    if (!issueForm.user_id || !issueForm.book_id) {
      Alert.alert('Error', 'Please select user and book');
      return;
    }

    if (!issueForm.issue_duration_days || issueForm.issue_duration_days < 1) {
      Alert.alert('Error', 'Please enter valid duration (1-365 days)');
      return;
    }

    try {
      await apiService.staff.issueBook({
        user_id: parseInt(issueForm.user_id),
        book_id: parseInt(issueForm.book_id),
        borrow_date: issueForm.borrow_date.toISOString().split('T')[0],
        issue_duration_days: parseInt(issueForm.issue_duration_days),
      });
      Alert.alert('Success', 'Book issued successfully');
      setIssueModalVisible(false);
      setIssueForm({
        user_id: '',
        book_id: '',
        borrow_date: new Date(),
        issue_duration_days: 15,
      });
      setCalculatedDueDate(null);
      loadBorrows();
    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || error.message || 'Failed to issue book');
    }
  };

  const handleReturn = async (borrow) => {
    const dueDate = new Date(borrow.due_date);
    const today = new Date();
    const daysOverdue = Math.ceil((today - dueDate) / (1000 * 60 * 60 * 24));
    const finePerDay = borrow.fine_per_day || 10;
    const estimatedFine = daysOverdue > 0 ? daysOverdue * finePerDay : 0;
    
    const message = daysOverdue > 0 
      ? `Return "${borrow.book?.title}"?\n\nOverdue: ${daysOverdue} day(s)\nEstimated Fine: ₹${estimatedFine.toFixed(2)}`
      : `Return "${borrow.book?.title}" borrowed by ${borrow.user?.name}?`;
    
    Alert.alert(
      'Return Book',
      message,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Return',
          onPress: async () => {
            try {
              await apiService.staff.returnBook(borrow.id);
              Alert.alert('Success', daysOverdue > 0 
                ? `Book returned successfully! Fine of ₹${estimatedFine.toFixed(2)} has been applied.`
                : 'Book returned successfully!'
              );
              loadBorrows();
            } catch (error) {
              Alert.alert('Error', error.response?.data?.message || error.message || 'Failed to return book');
            }
          },
        },
      ]
    );
  };

  const handleExtend = (borrow) => {
    Alert.prompt(
      'Extend Due Date',
      'Enter additional days (1-30):',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Extend',
          onPress: async (days) => {
            const additionalDays = parseInt(days);
            if (!additionalDays || additionalDays < 1 || additionalDays > 30) {
              Alert.alert('Error', 'Please enter a valid number between 1 and 30');
              return;
            }
            try {
              await apiService.staff.extendBorrow(borrow.id, additionalDays);
              Alert.alert('Success', `Due date extended by ${additionalDays} day(s)`);
              loadBorrows();
            } catch (error) {
              Alert.alert('Error', error.response?.data?.message || error.message || 'Failed to extend due date');
            }
          },
        },
      ],
      'plain-text',
      '5'
    );
  };

  const openIssueModal = async () => {
    try {
      const [usersData, booksData] = await Promise.all([
        apiService.staff.getStudents(),
        apiService.staff.getBooks(),
      ]);
      setUsers(usersData.filter(u => u.role === 'student').map(u => ({ label: `${u.name} (${u.email})`, value: u.id })));
      setBooks(booksData.filter(b => b.status === 'available' && b.available_copies > 0).map(b => ({ label: `${b.title} (Available: ${b.available_copies})`, value: b.id })));
      setIssueModalVisible(true);
    } catch (error) {
      Alert.alert('Error', 'Failed to load data');
    }
  };

  const renderBorrow = ({ item }) => {
    const dueDate = new Date(item.due_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    dueDate.setHours(0, 0, 0, 0);
    const daysDiff = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
    
    return (
      <View style={styles.card}>
        <View style={styles.cardContent}>
          <Text style={styles.title}>{item.book?.title}</Text>
          <Text style={styles.subtitle}>Borrowed by: {item.user?.name}</Text>
          <Text style={styles.email}>{item.user?.email}</Text>
          <View style={styles.metaRow}>
            <Text style={styles.date}>Borrowed: {new Date(item.borrow_date).toLocaleDateString()}</Text>
            <Text style={[styles.date, item.status === 'borrowed' && daysDiff < 0 ? styles.overdue : null]}>
              Due: {new Date(item.due_date).toLocaleDateString()}
            </Text>
          </View>
          {item.status === 'borrowed' && (
            <View style={styles.statusRow}>
              {daysDiff < 0 ? (
                <>
                  <Text style={[styles.status, styles.overdueStatus]}>
                    {Math.abs(daysDiff)} day(s) overdue
                  </Text>
                  <Text style={styles.fineText}>
                    Fine: ₹{((Math.abs(daysDiff) * (item.fine_per_day || 10)).toFixed(2))}
                  </Text>
                </>
              ) : daysDiff === 0 ? (
                <Text style={[styles.status, styles.dueToday]}>Due Today</Text>
              ) : (
                <Text style={[styles.status, styles.active]}>{daysDiff} day(s) left</Text>
              )}
            </View>
          )}
          <Text style={[styles.status, item.status === 'borrowed' ? styles.active : styles.returned]}>
            {item.status?.toUpperCase()}
          </Text>
        </View>
        {item.status === 'borrowed' && (
          <View style={styles.actionButtons}>
            <TouchableOpacity 
              style={styles.extendButton} 
              onPress={() => handleExtend(item)}
            >
              <Ionicons name="calendar" size={20} color="#48bb78" />
            </TouchableOpacity>
            <TouchableOpacity style={styles.returnButton} onPress={() => handleReturn(item)}>
              <Ionicons name="checkmark-circle" size={24} color="#28a745" />
            </TouchableOpacity>
          </View>
        )}
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#48bb78" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Issue/Return</Text>
        <TouchableOpacity style={styles.addButton} onPress={openIssueModal}>
          <Ionicons name="add" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      <FlatList
        data={borrows}
        renderItem={renderBorrow}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={styles.listContent}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No borrows found</Text>
          </View>
        }
      />

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
              <View style={styles.formGroup}>
                <Text style={styles.label}>Student *</Text>
                <SearchableDropdown
                  options={users}
                  selectedValue={issueForm.user_id}
                  onSelect={(value) => setIssueForm({ ...issueForm, user_id: value })}
                  placeholder="Select student"
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Book *</Text>
                <SearchableDropdown
                  options={books}
                  selectedValue={issueForm.book_id}
                  onSelect={(value) => setIssueForm({ ...issueForm, book_id: value })}
                  placeholder="Select book"
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Issue Date *</Text>
                <TouchableOpacity
                  style={styles.dateButton}
                  onPress={() => setShowDatePicker(true)}
                >
                  <Text style={styles.dateText}>
                    {issueForm.borrow_date.toLocaleDateString()}
                  </Text>
                  <Ionicons name="calendar" size={20} color="#48bb78" />
                </TouchableOpacity>
                {showDatePicker && (
                  <DateTimePicker
                    value={issueForm.borrow_date}
                    mode="date"
                    display="default"
                    onChange={(event, selectedDate) => {
                      setShowDatePicker(false);
                      if (selectedDate) {
                        setIssueForm({ ...issueForm, borrow_date: selectedDate });
                      }
                    }}
                  />
                )}
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Issue Duration (Days) *</Text>
                <TextInput
                  style={styles.input}
                  value={issueForm.issue_duration_days.toString()}
                  onChangeText={(text) => {
                    const num = parseInt(text) || 0;
                    if (num >= 1 && num <= 365) {
                      setIssueForm({ ...issueForm, issue_duration_days: num });
                    }
                  }}
                  keyboardType="numeric"
                  placeholder="Enter days (1-365)"
                  placeholderTextColor="#999"
                />
                {calculatedDueDate && (
                  <Text style={styles.dueDatePreview}>
                    Due Date: {calculatedDueDate.toLocaleDateString()}
                  </Text>
                )}
                <Text style={styles.helpText}>
                  Days will be counted from the next day after issue date
                </Text>
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
    backgroundColor: '#48bb78',
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
    padding: 8,
    borderRadius: 8,
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
    marginBottom: 10,
  },
  metaRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  date: {
    fontSize: 12,
    color: '#666',
  },
  status: {
    padding: 5,
    borderRadius: 5,
    alignSelf: 'flex-start',
    fontSize: 11,
    fontWeight: '600',
  },
  active: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  returned: {
    backgroundColor: '#f8d7da',
    color: '#721c24',
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 5,
    marginBottom: 5,
    gap: 10,
  },
  overdueStatus: {
    backgroundColor: '#f8d7da',
    color: '#721c24',
  },
  dueToday: {
    backgroundColor: '#fff3cd',
    color: '#856404',
  },
  fineText: {
    fontSize: 12,
    color: '#dc3545',
    fontWeight: '600',
  },
  overdue: {
    color: '#dc3545',
    fontWeight: 'bold',
  },
  actionButtons: {
    flexDirection: 'row',
    gap: 10,
    alignItems: 'center',
  },
  extendButton: {
    padding: 8,
    backgroundColor: '#e7f3ff',
    borderRadius: 8,
  },
  returnButton: {
    padding: 8,
    justifyContent: 'center',
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
    backgroundColor: '#f5f5f5',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#e0e0e0',
    color: '#333',
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
  dueDatePreview: {
    marginTop: 8,
    fontSize: 14,
    color: '#48bb78',
    fontWeight: '600',
  },
  helpText: {
    marginTop: 4,
    fontSize: 12,
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
  cancelButton: {
    backgroundColor: '#e0e0e0',
  },
  cancelButtonText: {
    color: '#333',
    fontWeight: '600',
  },
  submitButton: {
    backgroundColor: '#48bb78',
  },
  submitButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
});
