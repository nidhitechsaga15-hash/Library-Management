import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, ActivityIndicator, RefreshControl } from 'react-native';
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
      const data = await apiService.student.getFines();
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

  const renderFine = ({ item }) => (
    <View style={styles.card}>
      <Text style={styles.title}>₹{item.amount}</Text>
      <Text style={styles.reason}>{item.reason}</Text>
      <Text style={styles.date}>Date: {new Date(item.created_at).toLocaleDateString()}</Text>
      <Text style={[styles.status, item.status === 'paid' ? styles.paid : styles.pending]}>
        {item.status}
      </Text>
    </View>
  );

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#4299e1" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Fine History</Text>
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
  header: { backgroundColor: '#4299e1', padding: 20, paddingTop: 50 },
  headerTitle: { color: '#fff', fontSize: 24, fontWeight: 'bold' },
  card: { backgroundColor: '#fff', padding: 15, margin: 15, borderRadius: 12 },
  title: { fontSize: 20, fontWeight: 'bold', marginBottom: 5 },
  reason: { fontSize: 14, color: '#666', marginBottom: 5 },
  date: { fontSize: 12, color: '#999', marginBottom: 10 },
  status: { padding: 5, borderRadius: 5, alignSelf: 'flex-start' },
  paid: { backgroundColor: '#d4edda', color: '#155724' },
  pending: { backgroundColor: '#fff3cd', color: '#856404' },
});

