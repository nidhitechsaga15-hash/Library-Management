import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useIsFocused } from '@react-navigation/native';
import { CameraView, useCameraPermissions } from 'expo-camera';

export default function QRScannerScreen({ navigation, route }) {
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);
  const [loading, setLoading] = useState(false);
  const isFocused = useIsFocused();

  const fetchBookFromISBN = async (isbn) => {
    try {
      // Clean ISBN - remove hyphens and spaces
      const cleanISBN = isbn.replace(/[-\s]/g, '');
      
      // Try Google Books API
      const googleBooksUrl = `https://www.googleapis.com/books/v1/volumes?q=isbn:${cleanISBN}`;
      const response = await fetch(googleBooksUrl);
      const data = await response.json();

      if (data.items && data.items.length > 0) {
        const book = data.items[0].volumeInfo;
        
        // Extract authors
        const authors = book.authors || [];
        const authorName = authors.length > 0 ? authors[0] : '';
        
        // Extract publication year
        const publishedDate = book.publishedDate || '';
        const year = publishedDate ? parseInt(publishedDate.split('-')[0]) : '';
        
        // Extract page count
        const pageCount = book.pageCount || '';
        
        // Extract description
        const description = book.description || '';
        
        // Extract publisher
        const publisher = book.publisher || '';
        
        // Extract language
        const language = book.language || 'English';
        
        // Extract categories/subjects
        const categories = book.categories || [];
        const category = categories.length > 0 ? categories[0] : '';

        return {
          isbn: cleanISBN,
          title: book.title || '',
          author: authorName,
          authors: authors,
          publisher: publisher,
          publication_year: year,
          pages: pageCount,
          description: description,
          language: language,
          category: category,
          imageUrl: book.imageLinks?.thumbnail || book.imageLinks?.smallThumbnail || '',
        };
      }
      
      return null;
    } catch (error) {
      console.error('Error fetching book from Google Books:', error);
      return null;
    }
  };

  const handleBarCodeScanned = async ({ type, data }) => {
    if (scanned || loading) return;
    
    setScanned(true);
    setLoading(true);

    try {
      // Parse QR code/barcode data - could be ISBN, JSON, or other format
      let isbn = data;
      let bookData = null;

      // Try to parse as JSON first (if QR contains structured data)
      try {
        const parsed = JSON.parse(data);
        if (parsed.isbn || parsed.ISBN) {
          isbn = parsed.isbn || parsed.ISBN;
          bookData = parsed;
        }
      } catch (e) {
        // Not JSON, treat as plain ISBN/barcode
        isbn = data.trim();
      }

      // Validate that we have some data
      if (!isbn || isbn.length === 0) {
        Alert.alert('Invalid Code', 'Please scan a valid QR code or barcode');
        setScanned(false);
        setLoading(false);
        return;
      }

      // Fetch book information from Google Books API
      const fetchedBookData = await fetchBookFromISBN(isbn);
      
      // Prepare scanned book data
      const scannedBookData = {
        isbn: isbn,
        title: fetchedBookData?.title || bookData?.title || '',
        author: fetchedBookData?.author || bookData?.author || '',
        authors: fetchedBookData?.authors || [],
        publisher: fetchedBookData?.publisher || bookData?.publisher || '',
        publication_year: fetchedBookData?.publication_year || bookData?.publication_year || bookData?.year || '',
        pages: fetchedBookData?.pages || bookData?.pages || '',
        description: fetchedBookData?.description || bookData?.description || '',
        language: fetchedBookData?.language || bookData?.language || 'English',
        category: fetchedBookData?.category || bookData?.category || '',
        imageUrl: fetchedBookData?.imageUrl || '',
      };

      // Navigate to CreateBookScreen with scanned data
      navigation.navigate('CreateBook', { scannedData: scannedBookData });
    } catch (error) {
      console.error('Error processing QR/barcode:', error);
      Alert.alert('Error', 'Failed to process code. Please try again.');
      setScanned(false);
    } finally {
      setLoading(false);
    }
  };

  if (!permission) {
    return (
      <View style={styles.container}>
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#667eea" />
          <Text style={styles.messageText}>Requesting camera permission...</Text>
        </View>
      </View>
    );
  }

  if (!permission.granted) {
    return (
      <View style={styles.container}>
        <View style={styles.centerContainer}>
          <Ionicons name="camera-outline" size={64} color="#999" />
          <Text style={styles.messageText}>Camera permission is required</Text>
          <Text style={[styles.messageText, { fontSize: 14, marginTop: 10 }]}>
            Please grant camera permission to scan QR codes and barcodes
          </Text>
          <TouchableOpacity
            style={styles.button}
            onPress={requestPermission}
          >
            <Text style={styles.buttonText}>Grant Permission</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Scan QR/Barcode</Text>
        <View style={{ width: 24 }} />
      </View>

      <View style={styles.scannerContainer}>
        {isFocused && (
          <CameraView
            style={StyleSheet.absoluteFillObject}
            facing="back"
            onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
          />
        )}
        
        <View style={styles.overlay}>
          <View style={styles.scanArea}>
            <View style={[styles.corner, styles.topLeft]} />
            <View style={[styles.corner, styles.topRight]} />
            <View style={[styles.corner, styles.bottomLeft]} />
            <View style={[styles.corner, styles.bottomRight]} />
          </View>
        </View>

        {loading && (
          <View style={styles.loadingOverlay}>
            <ActivityIndicator size="large" color="#fff" />
            <Text style={styles.loadingText}>Processing...</Text>
          </View>
        )}

        <View style={styles.instructions}>
          <Text style={styles.instructionText}>
            Position the QR code or barcode within the frame
          </Text>
          <Text style={[styles.instructionText, { fontSize: 12, marginTop: 5, opacity: 0.8 }]}>
            Supports: QR Code, EAN-13, EAN-8, UPC, Code128, Code39, and more
          </Text>
          {scanned && (
            <TouchableOpacity
              style={styles.scanAgainButton}
              onPress={() => {
                setScanned(false);
                setLoading(false);
              }}
            >
              <Text style={styles.scanAgainText}>Tap to Scan Again</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
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
  scannerContainer: {
    flex: 1,
    position: 'relative',
  },
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  scanArea: {
    width: 250,
    height: 250,
    position: 'relative',
  },
  corner: {
    position: 'absolute',
    width: 30,
    height: 30,
    borderColor: '#667eea',
    borderWidth: 3,
  },
  topLeft: {
    top: 0,
    left: 0,
    borderRightWidth: 0,
    borderBottomWidth: 0,
  },
  topRight: {
    top: 0,
    right: 0,
    borderLeftWidth: 0,
    borderBottomWidth: 0,
  },
  bottomLeft: {
    bottom: 0,
    left: 0,
    borderRightWidth: 0,
    borderTopWidth: 0,
  },
  bottomRight: {
    bottom: 0,
    right: 0,
    borderLeftWidth: 0,
    borderTopWidth: 0,
  },
  instructions: {
    position: 'absolute',
    bottom: 50,
    left: 0,
    right: 0,
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  instructionText: {
    color: '#fff',
    fontSize: 16,
    textAlign: 'center',
    marginBottom: 10,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    padding: 12,
    borderRadius: 8,
  },
  scanAgainButton: {
    backgroundColor: '#667eea',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    marginTop: 10,
  },
  scanAgainText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    color: '#fff',
    marginTop: 10,
    fontSize: 16,
  },
  messageText: {
    color: '#fff',
    fontSize: 16,
    marginTop: 20,
    textAlign: 'center',
  },
  button: {
    backgroundColor: '#667eea',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    marginTop: 20,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});
