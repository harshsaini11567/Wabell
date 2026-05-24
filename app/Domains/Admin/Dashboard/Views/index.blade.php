@extends('Layouts::app')
@section('title', __('global.dashboard'))

@section('custom_css')
<style>
    #tooltip {
        position: absolute;
        display: inline-block;
        font-family: Gill Sans, sans-serif;
        font-size: 0.8rem;
        background-color: white;
        border: 2px solid black;
        border-radius: 2px;
        padding: 8px;
        text-align: left;
        z-index: 1;
        pointer-events: auto;
    }
    .cell:hover {
        stroke: darkslategrey;
        stroke-width: 2;
        opacity: 1;
    }
</style>
@endsection

@section('main-content')

 
<div class="row">
    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-primary">
            <div class="card-body">
                <a class="dashboard-card" href="{{route('masters.index')}}">
                    <div class="float-end">
                        <i class="ri-shield-user-line widget-icon"></i>
                    </div>
                    <h6 class="text-uppercase mt-0" title="@lang('cruds.master.title')">@lang('cruds.master.title')</h6>
                    <h2 class="my-2">{{ $masterCount }}</h2>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-purple">
            <div class="card-body">
                <a class="dashboard-card" href="{{route('customers.index')}}">
                    <div class="float-end">
                        <i class="ri-team-line widget-icon"></i>
                    </div>
                    <h6 class="text-uppercase mt-0" title="@lang('cruds.customer.title')">@lang('cruds.customer.title')</h6>
                    <h2 class="my-2">{{$customerCount}}</h2>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-purple">
            <div class="card-body">
                <a class="dashboard-card" href="{{route('specialties.index')}}">
                    <div class="float-end">
                        <i class="ri-star-line widget-icon"></i>
                    </div>
                    <h6 class="text-uppercase mt-0" title="@lang('cruds.customer.title')">@lang('cruds.specialty.title')</h6>
                    <h2 class="my-2">{{$specialityCount}}</h2>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-purple">
            <div class="card-body">
                <a class="dashboard-card" href="{{route('transactions.list')}}">
                    <div class="float-end">
                        <i class="ri-bank-card-line widget-icon"></i>
                    </div>
                    <h6 class="text-uppercase mt-0" title="@lang('cruds.customer.title')">@lang('cruds.transaction.title')</h6>
                    <h2 class="my-2">{{$transactionRevanue}}</h2>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="page-title-box my-3">
            <h4 class="page-title">Master Table</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive common_table h-400px">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Specialty / City</th>
                                @foreach($cities as $city)
                                    <th>{{ $city->name_en ?? 'N/A' }}</th>
                                @endforeach
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $columnTotals = [];
                                $grandTotal = 0;
                            @endphp
                            @foreach($specialities as $specialty)
                            @php $rowTotal = 0; @endphp
                                <tr>
                                    <td>{{ $specialty['name_en'] }}</td>
                                    @foreach($cities as $city)
                                        @php
                                        $count = ($masterCitySpecility[$city->id] ?? collect())
                                                ->firstWhere('specialty_id', $specialty->id)->total ?? 0;
                                            $rowTotal += $count;
                                            $columnTotals[$city->id] = ($columnTotals[$city->id] ?? 0) + $count;
                                            $grandTotal += $count;
                                        @endphp
                                        <td>{{ $count }}</td>
                                        <!-- <td>{{ ($masterCitySpecility[$city->city_id] ?? collect())->firstWhere('specialty_id', $specialty->id)->total ?? 0 }}</td> -->
                                    @endforeach
                                    <td>{{ $rowTotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                @foreach($cities as $city)
                                    <td><strong>{{ $columnTotals[$city->id] ?? 0 }}</strong></td>
                                @endforeach
                                <td><strong>{{ $grandTotal }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="page-title-box my-3">
            <h4 class="page-title">Learner Table</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive common_table h-400px">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Specialty / City</th>
                                @foreach($cities as $city)
                                    <th>{{ $city->name_en ?? 'N/A' }}</th>
                                @endforeach
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $columnTotals = [];
                                $grandTotal = 0;
                            @endphp
                            @foreach($specialities as $specialty)
                            @php $rowTotal = 0; @endphp
                                <tr>
                                    <td>{{ $specialty['name_en'] }}</td>
                                    @foreach($cities as $city)
                                        @php
                                        $count = ($customerCitySpecility[$city->id] ?? collect())
                                                ->firstWhere('specialty_id', $specialty->id)->total ?? 0;
                                            $rowTotal += $count;
                                            $columnTotals[$city->id] = ($columnTotals[$city->id] ?? 0) + $count;
                                            $grandTotal += $count;
                                        @endphp
                                        <td>{{ $count }}</td>
                                        <!-- <td>{{ ($customerCitySpecility[$city->city_id] ?? collect())->firstWhere('specialty_id', $specialty->id)->total ?? 0 }}</td> -->
                                    @endforeach
                                    <td>{{ $rowTotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                @foreach($cities as $city)
                                    <td><strong>{{ $columnTotals[$city->id] ?? 0 }}</strong></td>
                                @endforeach
                                <td><strong>{{ $grandTotal }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="page-title-box my-3">
            <h4 class="page-title">Learner:Master Ratio (Heatmap)</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div id="visualization" class="text-center"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom_js')
    <script type="module">
        import * as d3 from "https://cdn.jsdelivr.net/npm/d3@7/+esm";

        fetch("/heatmap-data")
        .then(res => res.json())
        .then(dataSet => {

            const cities = [...new Set(dataSet.map(d => d.city))];
            const specialties = [...new Set(dataSet.map(d => d.specialty))];
            const ratios = dataSet.map(d => d.ratio).filter(r => r !== null);

            const minRatio = d3.min(ratios);
            const maxRatio = d3.max(ratios);

            // Dimensions
            const margin = {top: 50, right: 30, bottom: 100, left: 300};
            const width = 1200 - margin.left - margin.right;
            const height = 600 - margin.top - margin.bottom;

            const svg = d3.select("#visualization")
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", `translate(${margin.left},${margin.top})`);

            // X = cities
            const xScale = d3.scaleBand()
            .domain(cities)
            .range([0, width])
            .padding(0.05);

            svg.append("g")
            .attr("transform", `translate(0,${height})`)
            .call(d3.axisBottom(xScale))
            .selectAll("text")
            .style("font-size", "12px")  // increase font size
            // .attr("transform", "rotate(-30)")  // optional: rotate if names are long
            // .style("text-anchor", "end")
            ;

            // Y = specialties
            const yScale = d3.scaleBand()
            .domain(specialties)
            .range([0, height])
            .padding(0.05);

            svg.append("g")
            .call(d3.axisLeft(yScale))
            .selectAll("text")
            .style("font-size", "12px");

            // Color scale
            const colorScale = d3.scaleQuantize()
                .domain([minRatio, maxRatio])   // input range
                .range(['#4CAF50', '#8BC34A', '#FFEB3B', '#FF9800', '#F44336']);
            // Tooltip
            const tooltip = d3.select("body")
            .append("div")
            .style("position", "absolute")
            .style("background", "white")
            .style("padding", "5px")
            .style("border", "1px solid #cccccc")
            .style("opacity", 0);

            // Draw heatmap cells
            svg.selectAll()
            .data(dataSet)
            .join("rect")
            .attr("x", d => xScale(d.city))
            .attr("y", d => yScale(d.specialty))
            .attr("width", xScale.bandwidth())
            .attr("height", yScale.bandwidth())
            .style("fill", d => d.ratio === null ? "#e2e6ef" : colorScale(d.ratio))
            .on("mouseover", (event, d) => {
                tooltip.style("opacity", 1)
                .html(`<strong>City:</strong> ${d.city}<br>
                        <strong>Specialty:</strong> ${d.specialty}<br>
                        <strong>Learner:</strong> ${d.student}<br>
                        <strong>Master:</strong> ${d.master}<br>
                        <strong>Ratio:</strong> ${d.ratio ?? "Not divisible"}`)
                .style("left", event.pageX + 10 + "px")
                .style("top", event.pageY - 20 + "px");
            })
            .on("mousemove", (event) => {   // keep tooltip following mouse
                const tooltipNode = tooltip.node();
                const tooltipWidth = tooltipNode.offsetWidth;
                const tooltipHeight = tooltipNode.offsetHeight;
                const pageWidth = window.innerWidth;
                const pageHeight = window.innerHeight;

                let left = event.pageX + 15;
                let top = event.pageY - 20;

                // Prevent going off right edge
                if (left + tooltipWidth > pageWidth) {
                    left = event.pageX - tooltipWidth - 15;
                }

                // Prevent going off bottom edge
                if (top + tooltipHeight > pageHeight) {
                    top = event.pageY - tooltipHeight - 15;
                }

                tooltip.style("left", left + "px")
                    .style("top", top + "px");
            })
            .on("mouseleave", () => tooltip.style("opacity", 0));
        });
    </script>

@endsection