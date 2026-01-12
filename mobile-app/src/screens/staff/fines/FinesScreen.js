import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { apiService } from '../../../services/apiService';

export default function FinesScreen() {
  const [fines, setFines] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadFines();
  }, []);

  const loadFines = async () => {
    try {
      const data = await apiService.staff.getFines();
      setFines(data);
    } catch (error) {
      console.error('Error loading fines:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadFines();
  };

  const handleUpdateStatus = async (id, status) => {
    try {
      await apiService.staff.updateFineStatus(id, status);
      loadFines();
    } catch (error) {
      console.error('Error updating fine status:', error);
    }
  };

  const renderFine = ({ item }) => (
    <View style={styles.card}>
      <Text style={styles.title}>₹{item.amount}</Text>
      <Text style={styles.subtitle}>User: {item.user?.name}</Text>
      <Text style={styles.reason}>{item.reason}</Text>
      <Text style={[styles.status, item.status === 'paid' ? styles.paid : styles.pending]}>
        {item.status}
      </Text>
      {item.status === 'pending' && (
        <TouchableOpacity
          style={styles.updateButton}
          onPress={() => handleUpdateStatus(item.id, 'paid')}
        >
          <Text style={styles.updateButtonText}>Mark as Paid</Text>
        </TouchableOpacity>
      )}
    </View>
  );

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
        <Text style={styles.headerTitle}>Fines</Text>
      </View>
      <FlatList
        data={fines}
        renderItem={renderFine}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  header: { backgroundColor: '#48bb78', padding: 20, paddingTop: 50 },
  headerTitle: { color: '#fff', fontSize: 24, fontWeight: 'bold' },
  card: { backgroundColor: '#fff', padding: 15, margin: 15, borderRadius: 12 },
  title: { fontSize: 20, fontWeight: 'bold', marginBottom: 5 },
  subtitle: { fontSize: 14, color: '#666', marginBottom: 5 },
  reason: { fontSize: 12, color: '#999', marginBottom: 10 },
  status: { padding: 5, borderRadius: 5, alignSelf: 'flex-start', marginBottom: 10 },
  paid: { backgroundColor: '#d4edda', color: '#155724' },
  pending: { backgroundColor: '#fff3cd', color: '#856404' },
  updateButton: { backgroundColor: '#48bb78', padding: 10, borderRadius: 8, marginTop: 10 },
  updateButtonText: { color: '#fff', textAlign: 'center', fontWeight: '600' },
});

