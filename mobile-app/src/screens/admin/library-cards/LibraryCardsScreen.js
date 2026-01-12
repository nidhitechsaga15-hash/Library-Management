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
import DateTimePicker from '@react-native-community/datetimepicker';

export default function LibraryCardsScreen({ navigation }) {
  const [cards, setCards] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [createModalVisible, setCreateModalVisible] = useState(false);
  const [users, setUsers] = useState([]);
  const [createForm, setCreateForm] = useState({
    user_id: '',
    expiry_date: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000), // 1 year from now
  });
  const [showDatePicker, setShowDatePicker] = useState(false);

  useEffect(() => {
    loadCards();
  }, []);

  const loadCards = async () => {
    try {
      const data = await apiService.admin.getLibraryCards();
      setCards(data);
    } catch (error) {
      console.error('Error loading library cards:', error);
      Alert.alert('Error', 'Failed to load library cards');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadCards();
  };

  const openCreateModal = async () => {
    try {
      const usersData = await apiService.admin.getUsers();
      const usersWithCards = cards.map(c => c.user_id);
      setUsers(usersData
        .filter(u => u.role === 'student' && !usersWithCards.includes(u.id))
        .map(u => ({ label: `${u.name} (${u.email})`, value: u.id })));
      setCreateForm({
        user_id: '',
        expiry_date: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000),
      });
      setCreateModalVisible(true);
    } catch (error) {
      Alert.alert('Error', 'Failed to load users');
    }
  };

  const handleCreate = async () => {
    if (!createForm.user_id) {
      Alert.alert('Error', 'Please select a user');
      return;
    }

    try {
      await apiService.admin.createLibraryCard({
        user_id: parseInt(createForm.user_id),
        expiry_date: createForm.expiry_date.toISOString().split('T')[0],
      });
      Alert.alert('Success', 'Library card created successfully');
      setCreateModalVisible(false);
      loadCards();
    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Failed to create library card');
    }
  };

  const handleBlock = async (card) => {
    Alert.alert(
      'Block Card',
      `Block library card for ${card.user?.name}?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Block',
          style: 'destructive',
          onPress: async () => {
            try {
              await apiService.admin.blockLibraryCard(card.id);
              Alert.alert('Success', 'Card blocked successfully');
              loadCards();
            } catch (error) {
              Alert.alert('Error', error.response?.data?.message || 'Failed to block card');
            }
          },
        },
      ]
    );
  };

  const handleUnblock = async (card) => {
    Alert.alert(
      'Unblock Card',
      `Unblock library card for ${card.user?.name}?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Unblock',
          onPress: async () => {
            try {
              await apiService.admin.unblockLibraryCard(card.id);
              Alert.alert('Success', 'Card unblocked successfully');
              loadCards();
            } catch (error) {
              Alert.alert('Error', error.response?.data?.message || 'Failed to unblock card');
            }
          },
        },
      ]
    );
  };

  const renderCard = ({ item }) => (
    <View style={styles.card}>
      <View style={styles.cardContent}>
        <View style={styles.cardHeader}>
          <Text style={styles.cardNumber}>Card: {item.card_number}</Text>
          <Text style={[styles.status, styles[`status${item.status}`]]}>
            {item.status?.toUpperCase()}
          </Text>
        </View>
        <Text style={styles.userName}>{item.user?.name}</Text>
        <Text style={styles.userEmail}>{item.user?.email}</Text>
        <Text style={styles.date}>
          Valid until: {new Date(item.validity_date).toLocaleDateString()}
        </Text>
        {item.issued_at && (
          <Text style={styles.date}>
            Issued: {new Date(item.issued_at).toLocaleDateString()}
          </Text>
        )}
      </View>
      <View style={styles.actionButtons}>
        {item.status === 'active' ? (
          <TouchableOpacity
            style={styles.blockButton}
            onPress={() => handleBlock(item)}
          >
            <Ionicons name="ban" size={24} color="#dc3545" />
          </TouchableOpacity>
        ) : (
          <TouchableOpacity
            style={styles.unblockButton}
            onPress={() => handleUnblock(item)}
          >
            <Ionicons name="checkmark-circle" size={24} color="#28a745" />
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
        <Text style={styles.headerTitle}>Library Cards</Text>
        <TouchableOpacity style={styles.addButton} onPress={openCreateModal}>
          <Ionicons name="add" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      <FlatList
        data={cards}
        renderItem={renderCard}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={styles.listContent}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No library cards found</Text>
          </View>
        }
      />

      <Modal
        visible={createModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setCreateModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Create Library Card</Text>
              <TouchableOpacity onPress={() => setCreateModalVisible(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody}>
              <View style={styles.formGroup}>
                <Text style={styles.label}>Student *</Text>
                <SearchableDropdown
                  options={users}
                  selectedValue={createForm.user_id}
                  onSelect={(value) => setCreateForm({ ...createForm, user_id: value })}
                  placeholder="Select student"
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Expiry Date *</Text>
                <TouchableOpacity
                  style={styles.dateButton}
                  onPress={() => setShowDatePicker(true)}
                >
                  <Text style={styles.dateText}>
                    {createForm.expiry_date.toLocaleDateString()}
                  </Text>
                  <Ionicons name="calendar" size={20} color="#667eea" />
                </TouchableOpacity>
                {showDatePicker && (
                  <DateTimePicker
                    value={createForm.expiry_date}
                    mode="date"
                    display="default"
                    minimumDate={new Date()}
                    onChange={(event, selectedDate) => {
                      setShowDatePicker(false);
                      if (selectedDate) {
                        setCreateForm({ ...createForm, expiry_date: selectedDate });
                      }
                    }}
                  />
                )}
              </View>
            </ScrollView>

            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={[styles.button, styles.cancelButton]}
                onPress={() => setCreateModalVisible(false)}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.button, styles.submitButton]}
                onPress={handleCreate}
              >
                <Text style={styles.submitButtonText}>Create Card</Text>
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
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  cardNumber: {
    fontSize: 18,
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
  statusactive: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  statusblocked: {
    backgroundColor: '#f8d7da',
    color: '#721c24',
  },
  statusexpired: {
    backgroundColor: '#fff3cd',
    color: '#856404',
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
  date: {
    fontSize: 12,
    color: '#999',
    marginBottom: 3,
  },
  actionButtons: {
    justifyContent: 'center',
  },
  blockButton: {
    padding: 8,
  },
  unblockButton: {
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
