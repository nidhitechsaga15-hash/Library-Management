import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, ActivityIndicator, RefreshControl } from 'react-native';
import { apiService } from '../../../services/apiService';

export default function ReservationsScreen() {
  const [reservations, setReservations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadReservations();
  }, []);

  const loadReservations = async () => {
    try {
      const data = await apiService.student.getReservations();
      setReservations(data);
    } catch (error) {
      console.error('Error loading reservations:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadReservations();
  };

  const renderReservation = ({ item }) => (
    <View style={styles.card}>
      <Text style={styles.title}>{item.book?.title}</Text>
      <Text style={styles.subtitle}>By {item.book?.author?.name}</Text>
      <Text style={styles.date}>Reserved on: {new Date(item.created_at).toLocaleDateString()}</Text>
      <Text style={[styles.status, item.status === 'active' ? styles.active : styles.inactive]}>
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
        <Text style={styles.headerTitle}>My Reservations</Text>
      </View>
      <FlatList
        data={reservations}
        renderItem={renderReservation}
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
  title: { fontSize: 18, fontWeight: 'bold', marginBottom: 5 },
  subtitle: { fontSize: 14, color: '#666', marginBottom: 5 },
  date: { fontSize: 12, color: '#999', marginBottom: 10 },
  status: { padding: 5, borderRadius: 5, alignSelf: 'flex-start' },
  active: { backgroundColor: '#d4edda', color: '#155724' },
  inactive: { backgroundColor: '#f8d7da', color: '#721c24' },
});

