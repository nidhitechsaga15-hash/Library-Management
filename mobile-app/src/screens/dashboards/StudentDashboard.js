import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../../context/AuthContext';
import { apiService } from '../../services/apiService';

export default function StudentDashboard() {
  const { user, logout } = useAuth();
  const navigation = useNavigation();
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    try {
      const data = await apiService.student.getDashboard();
      setStats(data);
    } catch (error) {
      console.error('Error loading dashboard:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadDashboard();
  };

  const handleActionPress = (action) => {
    try {
      switch (action) {
        case 'books':
          navigation.navigate('Books');
          break;
        case 'mybooks':
          navigation.navigate('MyBooks');
          break;
        case 'search':
          navigation.navigate('Books');
          break;
        case 'fines':
          navigation.navigate('More', { screen: 'Fines' });
          break;
        case 'librarycard':
          navigation.navigate('More', { screen: 'LibraryCard' });
          break;
        case 'chat':
          navigation.navigate('Chat');
          break;
        case 'notifications':
          navigation.navigate('More', { screen: 'Notifications' });
          break;
        case 'profile':
          navigation.navigate('More', { screen: 'Profile' });
          break;
        default:
          break;
      }
    } catch (error) {
      console.error('Navigation error:', error);
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#4299e1" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.greeting}>Welcome back,</Text>
          <Text style={styles.name}>{user?.name || 'Student'}</Text>
          {user?.course && (
            <Text style={styles.course}>{user.course} - {user.year}</Text>
          )}
        </View>
        <TouchableOpacity onPress={logout} style={styles.logoutButton}>
          <Text style={styles.logoutText}>Logout</Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.content}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      >
        {/* Stats Cards */}
        <View style={styles.statsContainer}>
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{stats?.issued_books || 0}</Text>
            <Text style={styles.statLabel}>Issued Books</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{stats?.pending_fines || 0}</Text>
            <Text style={styles.statLabel}>Pending Fines</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{stats?.reservations || 0}</Text>
            <Text style={styles.statLabel}>Reservations</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{stats?.available_books || 0}</Text>
            <Text style={styles.statLabel}>Available Books</Text>
          </View>
        </View>

        {/* Quick Actions */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          <View style={styles.actionsGrid}>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('books')}>
              <Text style={styles.actionIcon}>📚</Text>
              <Text style={styles.actionText}>Browse Books</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('mybooks')}>
              <Text style={styles.actionIcon}>📖</Text>
              <Text style={styles.actionText}>My Books</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('search')}>
              <Text style={styles.actionIcon}>🔍</Text>
              <Text style={styles.actionText}>Search</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('fines')}>
              <Text style={styles.actionIcon}>💰</Text>
              <Text style={styles.actionText}>Fines</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('librarycard')}>
              <Text style={styles.actionIcon}>🆔</Text>
              <Text style={styles.actionText}>Library Card</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('chat')}>
              <Text style={styles.actionIcon}>💬</Text>
              <Text style={styles.actionText}>Chat</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
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
    backgroundColor: '#4299e1',
    padding: 20,
    paddingTop: 50,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  greeting: {
    color: '#fff',
    fontSize: 14,
    opacity: 0.9,
  },
  name: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  course: {
    color: '#fff',
    fontSize: 12,
    opacity: 0.8,
    marginTop: 4,
  },
  logoutButton: {
    padding: 8,
    paddingHorizontal: 15,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 8,
  },
  logoutText: {
    color: '#fff',
    fontWeight: '600',
  },
  content: {
    flex: 1,
  },
  statsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 15,
    justifyContent: 'space-between',
  },
  statCard: {
    width: '48%',
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  statValue: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#4299e1',
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
  },
  section: {
    padding: 15,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  actionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  actionCard: {
    width: '31%',
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  actionIcon: {
    fontSize: 32,
    marginBottom: 8,
  },
  actionText: {
    fontSize: 12,
    color: '#333',
    fontWeight: '600',
    textAlign: 'center',
  },
});
