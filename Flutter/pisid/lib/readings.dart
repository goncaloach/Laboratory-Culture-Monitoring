import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;

import 'dart:convert';
import 'dart:async';
import 'dart:math';

class Readings extends StatelessWidget {
  const Readings({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    const appTitle = 'Readings';

    return Scaffold(
      appBar: AppBar(
        centerTitle: true,
        title: const Text(appTitle),
      ),
      body: const ReadingsMain(),
    );
  }
}

class ReadingsMain extends StatefulWidget {
  const ReadingsMain({Key? key}) : super(key: key);

  @override
  ReadingsMainState createState() {
    return ReadingsMainState();
  }
}

class ReadingsMainState extends State<ReadingsMain> {
  final List<Color> gradientColors = [
    const Color(0xff23b6e6),
    const Color(0xff02d39a),
  ];

  final List<Color> gradientColors2 = [
    const Color(0xffff9d00),
    const Color(0xffd3d002),
  ];

  late Timer timer;
  var readingsValues = <double>[];
  var readingsTimes = <double>[];

  var readingsValues2 = <double>[];
  var readingsTimes2 = <double>[];

  var minY = 0.0;
  var maxY = 30.0;
  double timeLimit = 3;

  @override
  void initState() {
    const interval = Duration(seconds: 1);
    timer = Timer.periodic(interval, (Timer t) => getReadings());
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        body: Container(
          margin: EdgeInsets.all(10),
          child: LineChart(
            LineChartData(
              minX: 0 - timeLimit,
              maxX: 0,
              minY: minY,
              maxY: maxY,
              titlesData: FlTitlesData(
                show: true,
                bottomTitles: SideTitles(
                  showTitles: true,
                  margin: 5,
                ),
                leftTitles: SideTitles(
                  showTitles: true,
                  margin: 5,
                  reservedSize: 30,
                ),
                rightTitles: SideTitles(
                  showTitles: false,
                ),
                topTitles: SideTitles(
                  showTitles: false,
                ),
              ),
              borderData: FlBorderData(
                show: true,
                border: Border.all(color: const Color(0xff37434d), width: 1),
              ),
              lineBarsData: [
                LineChartBarData(
                  spots: listReadings(),
                  dotData: FlDotData(show: false),
                  isCurved: false,
                  colors: gradientColors,
                  barWidth: 2,
                  belowBarData: BarAreaData(
                    show: false,
                    colors: gradientColors
                        .map((color) => color.withOpacity(0.2))
                        .toList(),
                  ),
                ),
                LineChartBarData(
                  spots: listReadings2(),
                  dotData: FlDotData(show: false),
                  isCurved: false,
                  colors: gradientColors2,
                  barWidth: 2,
                  belowBarData: BarAreaData(
                    show: false,
                    colors: gradientColors2
                        .map((color) => color.withOpacity(0.2))
                        .toList(),
                  ),
                ),
              ],
            ),
          ),
        ),

        bottomNavigationBar: BottomAppBar(
          child: ElevatedButton(
            onPressed: () {
              readingsValues.clear();
              readingsTimes.clear();


              readingsValues2.clear();
              readingsTimes2.clear();

              minY = 10.0;
              maxY = 30.0;
              Navigator.pop(context);
            },
            child: const Text('Alerts'),
          ),
        ));
  }

  getReadings() async {
    final prefs = await SharedPreferences.getInstance();
    String? username = prefs.getString('username');
    String? password = prefs.getString('password');
    String? ip = prefs.getString('ip');
    String? port = prefs.getString('port');

    String readingsURL =
        "http://" + ip! + ":" + port! + "/scripts/getReadings.php";
    var response = await http.post(Uri.parse(readingsURL),
        body: {'username': username, 'password': password});

    if (response.statusCode == 200) {
      var jsonData = json.decode(response.body);
      var data = jsonData["readings"];
      setState(() {
        readingsValues.clear();
        readingsTimes.clear();

        readingsValues2.clear();
        readingsTimes2.clear();

        minY = 10.0;
        maxY = 30.0;
        if (data != null && data.length > 0) {
          for (var reading in data) {
            DateTime currentTime = DateTime.now();
            if (reading["Sensor"].toString() == "T1") {
              DateTime readingTime =
              DateTime.parse(reading["DataHoraObjectId"].toString());
              double timeDiff = double.parse(
                  (currentTime
                      .difference(readingTime)
                      .inSeconds / 60)
                      .toStringAsFixed(2));
              //print("CURRENT: " + currentTime.toString());
              //print("READING: " + readingTime.toString());
              //print("DIFF: " + timeDiff.toString());
              //print("timeLimit: " + timeLimit.toString());

              if (timeDiff > 0.0 &&
                  timeDiff < timeLimit &&
                  !readingsTimes.contains(timeDiff)) {

                print("T1 IdMedicao: " + reading["IdMedicao"].toString());
                print("T1 VALUE: " + reading["Leitura"].toString());
                print(reading["Leitura"]);
                var value = double.parse(reading["Leitura"].toString());

                readingsTimes.add(timeDiff * -1);
                readingsValues.add(value);
              }
            }

            //T2
            if (reading["Sensor"].toString() == "T2") {
              DateTime readingTime2 =
                  DateTime.parse(reading["DataHoraObjectId2"].toString());
              double timeDiff2 = double.parse(
                  (currentTime.difference(readingTime2).inSeconds / 60)
                      .toStringAsFixed(2));
              //print("CURRENT: " + currentTime.toString());
              //print("READING: " + readingTime2.toString());
              //print("DIFF: " + timeDiff2.toString());
              //print("timeLimit: " + timeLimit.toString());

              if (timeDiff2 > 0.0 &&
                  timeDiff2 < timeLimit &&
                  !readingsTimes2.contains(timeDiff2)) {

                print("T2 IdMedicao: " + reading["IdMedicao"].toString());
                print("T2 VALUE: " + reading["Leitura2"].toString());
                print(reading["Leitura2"]);
                var value2 = double.parse(reading["Leitura2"].toString());

                readingsTimes2.add(timeDiff2 * -1);
                readingsValues2.add(value2);
              }
            }
          }

          if (readingsValues.isNotEmpty) {
            minY = readingsValues.reduce(min) - 1;
            maxY = readingsValues.reduce(max) + 1;
          }

          if (readingsValues2.isNotEmpty) {
            minY = readingsValues2.reduce(min) - 1 < minY
                ? readingsValues2.reduce(min) - 1
                : minY;
            maxY = readingsValues2.reduce(max) + 1 > maxY
                ? readingsValues2.reduce(max) + 1
                : maxY;
          }
        }
      });
    }
    print(" ");
  }

  listReadings() {
    var spots = <FlSpot>[];
    for (var i = 0; i < readingsValues.length; i++) {
      spots
          .add(FlSpot(readingsTimes.elementAt(i), readingsValues.elementAt(i)));
    }
    return spots;
  }

  listReadings2() {
    var spots = <FlSpot>[];
    for (var i = 0; i < readingsValues2.length; i++) {
      spots.add(
          FlSpot(readingsTimes2.elementAt(i), readingsValues2.elementAt(i)));
    }
    return spots;
  }

  @override
  void dispose() {
    timer.cancel();
    super.dispose();
  }
}
