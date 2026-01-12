import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import SearchableDropdown from '../../../components/SearchableDropdown';

export default function CreateBookScreen({ navigation, route }) {
  const [loading, setLoading] = useState(false);
  const [authors, setAuthors] = useState([]);
  const [categories, setCategories] = useState([]);
  const [formData, setFormData] = useState({
    isbn: '',
    title: '',
    author_id: '',
    category_id: '',
    publisher: '',
    edition: '',
    publication_year: '',
    total_copies: '',
    available_copies: '',
    rack_number: '',
    language: '',
    pages: '',
    status: 'available',
    description: '',
  });

  useEffect(() => {
    loadData();
  }, []);

  useEffect(() => {
    // Check if scanned data is passed from QR scanner
    if (route?.params?.scannedData && authors.length > 0) {
      const scanned = route.params.scannedData;
      
      // Find author by name
      let authorId = '';
      if (scanned.author) {
        const foundAuthor = authors.find(a => 
          a.label.toLowerCase().includes(scanned.author.toLowerCase()) ||
          scanned.author.toLowerCase().includes(a.label.toLowerCase())
        );
        if (foundAuthor) {
          authorId = foundAuthor.value;
        }
      }
      
      // Find category by name
      let categoryId = '';
      if (scanned.category && categories.length > 0) {
        const foundCategory = categories.find(c => 
          c.label.toLowerCase().includes(scanned.category.toLowerCase()) ||
          scanned.category.toLowerCase().includes(c.label.toLowerCase())
        );
        if (foundCategory) {
          categoryId = foundCategory.value;
        }
      }
      
      setFormData(prev => ({
        ...prev,
        isbn: scanned.isbn || prev.isbn,
        title: scanned.title || prev.title,
        author_id: authorId || prev.author_id,
        category_id: categoryId || prev.category_id,
        publisher: scanned.publisher || prev.publisher,
        edition: scanned.edition || prev.edition,
        publication_year: scanned.publication_year ? String(scanned.publication_year) : prev.publication_year,
        pages: scanned.pages ? String(scanned.pages) : prev.pages,
        language: scanned.language || prev.language,
        description: scanned.description || prev.description,
      }));
      
      // Show success message
      if (scanned.title) {
        Alert.alert(
          'Book Information Loaded',
          `Found: ${scanned.title}${scanned.author ? ` by ${scanned.author}` : ''}\n\nPlease review and complete the form.`,
          [{ text: 'OK' }]
        );
      }
    }
  }, [route?.params?.scannedData, authors, categories]);

  const loadData = async () => {
    try {
      const [authorsData, categoriesData] = await Promise.all([
        apiService.admin.getAuthors().catch(() => []),
        apiService.admin.getCategories().catch(() => []),
      ]);
      
      if (authorsData && authorsData.length > 0) {
        setAuthors(authorsData.map(a => ({ label: a.name || 'Unknown', value: a.id })));
      } else {
        setAuthors([]);
        Alert.alert('Warning', 'No authors found. Please add authors first.');
      }
      
      if (categoriesData && categoriesData.length > 0) {
        setCategories(categoriesData.map(c => ({ label: c.name || 'Unknown', value: c.id })));
      } else {
        setCategories([]);
        Alert.alert('Warning', 'No categories found. Please add categories first.');
      }
    } catch (error) {
      console.error('Error loading data:', error);
      Alert.alert('Error', 'Failed to load authors and categories');
    }
  };

  const handleSubmit = async () => {
    if (!formData.isbn || !formData.title || !formData.author_id || !formData.category_id || !formData.total_copies) {
      Alert.alert('Error', 'Please fill all required fields');
      return;
    }

    if (authors.length === 0) {
      Alert.alert('Error', 'No authors available. Please add authors first.');
      return;
    }

    if (categories.length === 0) {
      Alert.alert('Error', 'No categories available. Please add categories first.');
      return;
    }

    setLoading(true);
    try {
      const submitData = {
        isbn: formData.isbn.trim(),
        title: formData.title.trim(),
        author_id: parseInt(formData.author_id),
        category_id: parseInt(formData.category_id),
        total_copies: parseInt(formData.total_copies),
        available_copies: parseInt(formData.available_copies || formData.total_copies),
        status: formData.status,
        publisher: formData.publisher?.trim() || null,
        edition: formData.edition?.trim() || null,
        publication_year: formData.publication_year ? parseInt(formData.publication_year) : null,
        rack_number: formData.rack_number?.trim() || null,
        language: formData.language?.trim() || null,
        pages: formData.pages ? parseInt(formData.pages) : null,
        description: formData.description?.trim() || null,
      };

      const response = await apiService.admin.createBook(submitData);
      console.log('Book created successfully:', response);
      Alert.alert('Success', 'Book created successfully', [
        { text: 'OK', onPress: () => navigation.goBack() },
      ]);
    } catch (error) {
      console.error('Create book error:', error);
      console.error('Error response:', error.response?.data);
      
      let errorMessage = 'Failed to create book';
      if (error.response?.data) {
        if (error.response.data.errors) {
          const errors = error.response.data.errors;
          errorMessage = Object.values(errors).flat().join('\n');
        } else if (error.response.data.message) {
          errorMessage = error.response.data.message;
        }
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      Alert.alert('Error', errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Create Book</Text>
        <TouchableOpacity
          onPress={() => navigation.navigate('More', { screen: 'QRScanner' })}
        >
          <Ionicons name="qr-code-outline" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.content}>
        <View style={styles.formGroup}>
          <View style={styles.inputRow}>
            <View style={styles.inputWithButton}>
              <Text style={styles.label}>ISBN *</Text>
              <TextInput
                style={[styles.input, styles.inputFlex]}
                placeholder="Enter ISBN"
                value={formData.isbn}
                onChangeText={(text) => setFormData({ ...formData, isbn: text })}
              />
            </View>
            <TouchableOpacity
              style={styles.scanButton}
              onPress={() => navigation.navigate('More', { screen: 'QRScanner' })}
            >
              <Ionicons name="qr-code" size={24} color="#667eea" />
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Title *</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter book title"
            value={formData.title}
            onChangeText={(text) => setFormData({ ...formData, title: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Author *</Text>
          <SearchableDropdown
            options={authors}
            selectedValue={formData.author_id}
            onSelect={(value) => setFormData({ ...formData, author_id: value })}
            placeholder="Select author"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Category *</Text>
          <SearchableDropdown
            options={categories}
            selectedValue={formData.category_id}
            onSelect={(value) => setFormData({ ...formData, category_id: value })}
            placeholder="Select category"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Publisher</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter publisher"
            value={formData.publisher}
            onChangeText={(text) => setFormData({ ...formData, publisher: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Edition</Text>
          <TextInput
            style={styles.input}
            placeholder="e.g., 1st, 2nd, 3rd"
            value={formData.edition}
            onChangeText={(text) => setFormData({ ...formData, edition: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Publication Year</Text>
          <TextInput
            style={styles.input}
            placeholder="e.g., 2024"
            value={formData.publication_year}
            onChangeText={(text) => setFormData({ ...formData, publication_year: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Total Copies *</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter total copies"
            value={formData.total_copies}
            onChangeText={(text) => setFormData({ ...formData, total_copies: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Available Copies</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter available copies"
            value={formData.available_copies}
            onChangeText={(text) => setFormData({ ...formData, available_copies: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Rack Number</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter rack number"
            value={formData.rack_number}
            onChangeText={(text) => setFormData({ ...formData, rack_number: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Language</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter language"
            value={formData.language}
            onChangeText={(text) => setFormData({ ...formData, language: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Pages</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter number of pages"
            value={formData.pages}
            onChangeText={(text) => setFormData({ ...formData, pages: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Status *</Text>
          <SearchableDropdown
            options={[
              { label: 'Available', value: 'available' },
              { label: 'Unavailable', value: 'unavailable' },
            ]}
            selectedValue={formData.status}
            onSelect={(value) => setFormData({ ...formData, status: value })}
            placeholder="Select status"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Description</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            placeholder="Enter book description"
            value={formData.description}
            onChangeText={(text) => setFormData({ ...formData, description: text })}
            multiline
            numberOfLines={4}
          />
        </View>

        <TouchableOpacity
          style={[styles.submitButton, loading && styles.submitButtonDisabled]}
          onPress={handleSubmit}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.submitButtonText}>Create Book</Text>
          )}
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
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
    fontSize: 20,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
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
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  submitButton: {
    backgroundColor: '#667eea',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 20,
    marginBottom: 30,
  },
  submitButtonDisabled: {
    opacity: 0.6,
  },
  submitButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  inputRow: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: 10,
  },
  inputWithButton: {
    flex: 1,
  },
  inputFlex: {
    flex: 1,
  },
  scanButton: {
    width: 50,
    height: 50,
    backgroundColor: '#fff',
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#e0e0e0',
    marginBottom: 8,
  },
});

